<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Initialize $tab with default value BEFORE any output
$tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'users';

$user = [
    'first_name' => isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'User',
    'last_name' => isset($_SESSION['user_name']) ? (explode(' ', $_SESSION['user_name'])[1] ?? '') : '',
    'role' => $_SESSION['user_role'] ?? 'guest',
    'id' => $_SESSION['user_id']
];

function hasPermission($requiredRole) {
    $userRole = $_SESSION['user_role'] ?? 'guest';
    
    // Permission hierarchy
    $roles = [
        'super_admin' => 3,
        'hr_manager' => 2,
        'dept_head' => 1,
        'employee' => 0
    ];
    
    $userLevel = $roles[$userRole] ?? 0;
    $requiredLevel = $roles[$requiredRole] ?? 0;
    
    return $userLevel >= $requiredLevel;
}

// Only super admin or HR manager can access this page
if (!(hasPermission('super_admin') || hasPermission('hr_manager'))) {
    header('Location: dashboard.php');
    exit();
}

function formatDate($date) {
    if (!$date) return 'N/A';
    return date('M d, Y', strtotime($date));
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirectWithMessage($location, $message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: {$location}");
    exit();
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return false;
}

// Financial Year Helper Functions
function getCurrentFinancialYear($current_date = null) {
    if ($current_date === null) {
        $current_date = date('Y-m-d');
    }
    
    $current_year = date('Y', strtotime($current_date));
    $current_month = date('n', strtotime($current_date)); // 1-12
    
    if ($current_month >= 7) {
        // Current financial year: July current_year to June next_year
        $start_year = $current_year;
        $end_year = $current_year + 1;
    } else {
        // Current financial year: July previous_year to June current_year
        $start_year = $current_year - 1;
        $end_year = $current_year;
    }
    
    return [
        'start_date' => $start_year . '-07-01',
        'end_date' => $end_year . '-06-30',
        'year_name' => $start_year . '-' . $end_year
    ];
}

function getNextFinancialYear($current_date = null) {
    $current_fy = getCurrentFinancialYear($current_date);
    $start_year = explode('-', $current_fy['start_date'])[0];
    $next_start_year = $start_year + 1;
    $next_end_year = $next_start_year + 1;
    
    return [
        'start_date' => $next_start_year . '-07-01',
        'end_date' => $next_end_year . '-06-30',
        'year_name' => $next_start_year . '-' . $next_end_year
    ];
}

function canCreateNewFinancialYear($mysqli) {
    $current_date = date('Y-m-d');
    $current_fy = getCurrentFinancialYear($current_date);
    
    // Check if current date is past the current financial year end date
    $current_fy_ended = $current_date > $current_fy['end_date'];
    
    /*if (!$current_fy_ended) {
        return [
            'can_create' => false,
            'reason' => 'Current financial year (' . $current_fy['year_name'] . ') has not ended yet. It ends on ' . formatDate($current_fy['end_date']) . '.',
            'next_fy' => null
        ];
    }*/
    
    // Check if next financial year already exists
    $next_fy = getNextFinancialYear($current_date);
    $stmt = $mysqli->prepare("SELECT id FROM financial_years WHERE year_name = ?");
    $stmt->bind_param("s", $next_fy['year_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->fetch_assoc()) {
        return [
            'can_create' => false,
            'reason' => 'Financial year ' . $next_fy['year_name'] . ' already exists.',
            'next_fy' => null
        ];
    }
    
    return [
        'can_create' => true,
        'reason' => 'Ready to create next financial year.',
        'next_fy' => $next_fy
    ];
}

function allocateLeaveToAllEmployees($mysqli, $financial_year_id) {
    try {
        // Get all active leave types
        $leave_types_query = "SELECT id, name, max_days_per_year FROM leave_types WHERE is_active = 1";
        $leave_types_result = $mysqli->query($leave_types_query);
        
        // Get all employees
        $employees_query = "SELECT id FROM employees WHERE status = 'active' AND employment_type = 'permanent'";
        $employees_result = $mysqli->query($employees_query);
        
        $allocated_count = 0;
        
        if ($employees_result && $leave_types_result) {
            $employees = $employees_result->fetch_all(MYSQLI_ASSOC);
            $leave_types = $leave_types_result->fetch_all(MYSQLI_ASSOC);
            
            foreach ($employees as $employee) {
                foreach ($leave_types as $leave_type) {
                    // Skip leave types with NULL max_days (like Sick Leave, Short Leave)
                    if ($leave_type['max_days_per_year'] === null) {
                        continue;
                    }
                    
                    $allocated_days = (float)$leave_type['max_days_per_year'];
                    
                    // Check if allocation already exists
                    $check_stmt = $mysqli->prepare("SELECT id FROM employee_leave_balances WHERE employee_id = ? AND leave_type_id = ? AND financial_year_id = ?");
                    $check_stmt->bind_param("sii", $employee['id'], $leave_type['id'], $financial_year_id);
                    $check_stmt->execute();
                    $existing = $check_stmt->get_result()->fetch_assoc();
                    
                    if (!$existing) {
                        // Insert new allocation
                        $insert_stmt = $mysqli->prepare("INSERT INTO employee_leave_balances (employee_id, leave_type_id, financial_year_id, allocated_days, used_days, remaining_days, carried_forward) VALUES (?, ?, ?, ?, 0, ?, 0)");
                        $insert_stmt->bind_param("sidd", $employee['id'], $leave_type['id'], $financial_year_id, $allocated_days, $allocated_days);
                        
                        if ($insert_stmt->execute()) {
                            $allocated_count++;
                        }
                    }
                }
            }
        }
        
        return $allocated_count;
    } catch (Exception $e) {
        error_log("Error allocating leave: " . $e->getMessage());
        return 0;
    }
}

$mysqli = getConnection();

// Get financial year status
$fy_status = canCreateNewFinancialYear($mysqli);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add_financial_year') {
            // Only allow if conditions are met
            if (!$fy_status['can_create']) {
                $error = $fy_status['reason'];
            } else {
                $next_fy = $fy_status['next_fy'];
                $start_date = $next_fy['start_date'];
                $end_date = $next_fy['end_date'];
                $year_name = $next_fy['year_name'];
                
                try {
                    // Calculate total days
                    $start_timestamp = strtotime($start_date);
                    $end_timestamp = strtotime($end_date);
                    $total_days = ceil(($end_timestamp - $start_timestamp) / (60 * 60 * 24)) + 1;
                    
                    // Insert new financial year
                    $stmt = $mysqli->prepare("INSERT INTO financial_years (start_date, end_date, year_name, total_days, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->bind_param("sssi", $start_date, $end_date, $year_name, $total_days);
                    
                    if ($stmt->execute()) {
                        $financial_year_id = $mysqli->insert_id;
                        
                        // Allocate leave to all employees
                        $allocated_count = allocateLeaveToAllEmployees($mysqli, $financial_year_id);
                        
                        redirectWithMessage('admin.php?tab=financial', 
                            "Financial year '{$year_name}' created successfully! Leave allocated to {$allocated_count} employee-leave type combinations.", 
                            'success');
                    } else {
                        $error = 'Error creating financial year: ' . $mysqli->error;
                    }
                } catch (Exception $e) {
                    $error = 'Error creating financial year: ' . $e->getMessage();
                }
            }
        }
        
        // User management actions
        elseif ($action === 'add_user') {
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            $phone = sanitizeInput($_POST['phone']);
            $address = sanitizeInput($_POST['address']);
            $employee_id = sanitizeInput($_POST['employee_id']);
            
            try {
                // Check if email already exists
                $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->fetch_assoc()) {
                    $error = 'Email already exists in the system.';
                } else {
                    // Generate unique user ID based on role
                    $rolePrefix = substr($role, 0, 3);
                    $timestamp = time();
                    $userId = $rolePrefix . '-' . $timestamp;
                    
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $mysqli->prepare("INSERT INTO users (id, first_name, last_name, email, password, role, phone, address, employee_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->bind_param("sssssssss", $userId, $first_name, $last_name, $email, $hashedPassword, $role, $phone, $address, $employee_id);
                    $stmt->execute();
                    redirectWithMessage('admin.php?tab=users', 'User created successfully!', 'success');
                }
            } catch (Exception $e) {
                $error = 'Error creating user: ' . $mysqli->error;
            }
        } elseif ($action === 'edit_user') {
            $id = $_POST['id'];
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $email = sanitizeInput($_POST['email']);
            $role = $_POST['role'];
            $phone = sanitizeInput($_POST['phone']);
            $address = sanitizeInput($_POST['address']);
            $password = $_POST['password'];
            $employee_id = sanitizeInput($_POST['employee_id']);

            try {
                // Check if email exists for other users (exclude current user by id)
                $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->bind_param("ss", $email, $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->fetch_assoc()) {
                    $error = 'Email already exists for another user.';
                } else {
                    if (!empty($password)) {
                        // Update with password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $mysqli->prepare("UPDATE users SET first_name=?, last_name=?, email=?, password=?, role=?, phone=?, address=?, employee_id=?, updated_at=NOW() WHERE id=?");
                        $stmt->bind_param("sssssssss", $first_name, $last_name, $email, $hashedPassword, $role, $phone, $address, $employee_id, $id);
                    } else {
                        // Update without password
                        $stmt = $mysqli->prepare("UPDATE users SET first_name=?, last_name=?, email=?, role=?, phone=?, address=?, employee_id=?, updated_at=NOW() WHERE id=?");
                        $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $role, $phone, $address, $employee_id, $id);
                    }
                    $stmt->execute();
                    redirectWithMessage('admin.php?tab=users', 'User details successfully updated!', 'success');
                }
            } catch (Exception $e) {
                $error = 'Error updating user: ' . $mysqli->error;
            }
        } elseif ($action === 'delete_user') {
            $id = $_POST['id'];
            
            // Prevent deleting own account
            if ($id == $user['id']) {
                $error = 'You cannot delete your own account.';
            } else {
                $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("s", $id);
                if ($stmt->execute()) {
                    redirectWithMessage('admin.php?tab=users', 'User deleted successfully!', 'success');
                } else {
                    $error = 'Error deleting user: ' . $mysqli->error;
                }
            }
        }
    }
}

// Get all users
$result = $mysqli->query("SELECT * FROM users ORDER BY first_name, last_name");
$users = $result->fetch_all(MYSQLI_ASSOC);

// Get all financial years
$financial_years_result = $mysqli->query("SELECT * FROM financial_years ORDER BY start_date DESC");
$financial_years = $financial_years_result->fetch_all(MYSQLI_ASSOC);

function getRoleBadge($role) {
    switch($role) {
        case 'super_admin': return 'badge-danger';
        case 'hr_manager': return 'badge-warning';
        case 'dept_head': return 'badge-info';
        case 'section_head': return 'badge-secondary';
        case 'manager': return 'badge-primary';
        default: return 'badge-light';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - HR Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <h1>HR System</h1>
                <p>Management Portal</p>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="employees.php">Employees</a></li>
                    <?php if (hasPermission('hr_manager')): ?>
                    <li><a href="departments.php">Departments</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('super_admin') || hasPermission('hr_manager')): ?>
                    <li><a href="admin.php" class="active">Admin</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('hr_manager')): ?>
                    <li><a href="reports.php">Reports</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('hr_manager') || hasPermission('super_admin') || hasPermission('dept_head')): ?>
                    <li><a href="leave_management.php">Leave Management</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Admin Panel</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    <span class="badge badge-info"><?php echo ucwords(str_replace('_', ' ', $user['role'])); ?></span>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </div>
            
            <div class="content">
                <?php $flash = getFlashMessage(); if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="leave-tabs">
                    <?php if (in_array($user['role'], ['super_admin'])): ?>
                    <a href="admin.php?tab=users" class="leave-tab <?php echo $tab === 'users' ? 'active' : ''; ?>">Users</a>
                    <?php endif; ?>
                    <a href="admin.php?tab=financial" class="leave-tab <?php echo $tab === 'financial' ? 'active' : ''; ?>">Financial Year</a>
                </div>

                <?php if ($tab === 'users'): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>System Users (<?php echo count($users); ?>)</h2>
                    <button onclick="showAddUserModal()" class="btn btn-success">Add New User</button>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No users found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user_row): ?>
                                <tr>
                                    <td><?php echo $user_row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user_row['first_name'] . ' ' . $user_row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user_row['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getRoleBadge($user_row['role']); ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $user_row['role'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user_row['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-success">Active</span>
                                    </td>
                                    <td><?php echo formatDate($user_row['created_at']); ?></td>
                                    <td>
                                        <button onclick="showEditUserModal(<?php echo htmlspecialchars(json_encode($user_row)); ?>)" class="btn btn-sm btn-primary">Edit</button>
                                        <?php if ($user_row['id'] != $user['id']): ?>
                                            <button onclick="confirmDeleteUser('<?php echo $user_row['id']; ?>', '<?php echo htmlspecialchars($user_row['first_name'] . ' ' . $user_row['last_name']); ?>')" class="btn btn-sm btn-danger ml-1">Delete</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php elseif ($tab === 'financial'): ?>
                <div class="tab-content">
                    <h3>Financial Year Management</h3>
                    
                    <!-- Financial Year Status Info -->
                    <div class="glass-card financial-year-status">
                        <h4>Financial Year Status</h4>
                      
                    </div>
                    
                    <div class="glass-card">
                        <h4>Add New Financial Year</h4>
                        
                        <?php if (!$fy_status['can_create']): ?>
                            <div class="alert alert-info">
                                <strong>Note:</strong> <?php echo $fy_status['reason']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_financial_year">
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" 
                                           name="start_date" 
                                           id="start_date" 
                                           class="form-control" 
                                           value="<?php echo $fy_status['can_create'] ? $fy_status['next_fy']['start_date'] : ''; ?>"
                                           readonly
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" 
                                           name="end_date" 
                                           id="end_date" 
                                           class="form-control" 
                                           value="<?php echo $fy_status['can_create'] ? $fy_status['next_fy']['end_date'] : ''; ?>"
                                           readonly
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="calculated_days">Financial Year Details</label>
                                    <input type="text" 
                                           id="calculated_days" 
                                           class="form-control" 
                                           readonly 
                                           value="<?php echo $fy_status['can_create'] ? $fy_status['next_fy']['year_name'] . ' (365 days)' : 'Not available'; ?>"
                                           placeholder="Will be calculated automatically">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" 
                                        class="btn btn-primary" 
                                        <?php echo !$fy_status['can_create'] ? 'disabled' : ''; ?>>
                                    <?php echo $fy_status['can_create'] ? 'Add New Financial Year' : 'Cannot Add Financial Year'; ?>
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="location.reload()">Refresh Status</button>
                            </div>
                        </form>
                    </div>

                    <!-- Existing Financial Years -->
                    <div class="table-container">
                        <h3>Existing Financial Years</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Year Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Days</th>
                                    <th>Status</th>
                                    <th>Current Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($financial_years)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No financial years found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($financial_years as $fy): ?>
                                    <tr>
                                        <td><?php echo $fy['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($fy['year_name']); ?></strong></td>
                                        <td><?php echo formatDate($fy['start_date']); ?></td>
                                        <td><?php echo formatDate($fy['end_date']); ?></td>
                                        <td><?php echo $fy['total_days']; ?> days</td>
                                        <td>
                                            <span class="badge <?php echo $fy['is_active'] ? 'badge-success' : 'badge-secondary'; ?>">
                                                <?php echo $fy['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $today = date('Y-m-d');
                                            if ($today < $fy['start_date']) {
                                                echo '<span class="badge badge-info">Future</span>';
                                            } elseif ($today >= $fy['start_date'] && $today <= $fy['end_date']) {
                                                echo '<span class="badge badge-success">Current</span>';
                                            } else {
                                                echo '<span class="badge badge-secondary">Past</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo formatDate($fy['created_at']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- User Management Modals -->
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <span class="close" onclick="hideAddUserModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_user">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="hr_manager">HR Manager</option>
                            <option value="dept_head">Department Head</option>
                            <option value="section_head">Section Head</option>
                            <option value="manager">Manager</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="employee_id">Employee ID</label>
                        <input type="text" class="form-control" id="employee_id" name="employee_id">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Create User</button>
                    <button type="button" class="btn btn-secondary" onclick="hideAddUserModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <span class="close" onclick="hideEditUserModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" id="edit_user_id" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_first_name">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password">New Password</label>
                        <input type="password" class="form-control" id="edit_password" name="password" placeholder="Leave blank to keep current password">
                        <small class="form-text text-muted">Leave blank to keep current password</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_role">Role</label>
                        <select class="form-control" id="edit_role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="hr_manager">HR Manager</option>
                            <option value="dept_head">Department Head</option>
                            <option value="section_head">Section Head</option>
                            <option value="manager">Manager</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_employee_id">Employee ID</label>
                        <input type="text" class="form-control" id="edit_employee_id" name="employee_id" readonly>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_address">Address</label>
                    <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <button type="button" class="btn btn-secondary" onclick="hideEditUserModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // User modal functions
        function showAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
        }
        
        function hideAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }
        
        function showEditUserModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_first_name').value = user.first_name;
            document.getElementById('edit_last_name').value = user.last_name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_address').value = user.address || '';
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_employee_id').value = user.employee_id || '';
            document.getElementById('editUserModal').style.display = 'block';
        }
        
        function hideEditUserModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }
        
        function confirmDeleteUser(id, name) {
            if (confirm('Are you sure you want to delete user "' + name + '"?\n\nThis action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = ['addUserModal', 'editUserModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>

    </body>
</html>
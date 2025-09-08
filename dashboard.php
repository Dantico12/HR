<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

<<<<<<< HEAD
// Set page title for header
$pageTitle = 'Dashboard';

=======
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
// Get current user from session
$user = [
    'first_name' => isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'User',
    'last_name' => isset($_SESSION['user_name']) ? (explode(' ', $_SESSION['user_name'])[1] ?? '') : '',
    'role' => $_SESSION['user_role'] ?? 'guest',
    'id' => $_SESSION['user_id']
];

// Permission check function
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

// Helper functions
function getEmployeeTypeBadge($type) {
    $badges = [
        'full_time' => 'badge-primary',
        'part_time' => 'badge-info',
        'contract' => 'badge-warning',
        'temporary' => 'badge-secondary',
        'officer' => 'badge-primary',
        'section_head' => 'badge-info',
        'manager' => 'badge-success'
    ];
    return $badges[$type] ?? 'badge-light';
}

function getEmployeeStatusBadge($status) {
    $badges = [
        'active' => 'badge-success',
        'on_leave' => 'badge-warning',
        'terminated' => 'badge-danger',
        'resigned' => 'badge-secondary'
    ];
    return $badges[$status] ?? 'badge-light';
}

function formatDate($date) {
    if (!$date) return 'N/A';
    return date('M d, Y', strtotime($date));
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

// Get dashboard statistics
$conn = getConnection();

<<<<<<< HEAD
// Total employees
=======
// Total employees - fixed column name
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
$result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE employee_status = 'active'");
$totalEmployees = $result->fetch_assoc()['count'];

// Total departments
$result = $conn->query("SELECT COUNT(*) as count FROM departments");
$totalDepartments = $result->fetch_assoc()['count'];

// Total sections
$result = $conn->query("SELECT COUNT(*) as count FROM sections");
$totalSections = $result->fetch_assoc()['count'];

// Recent employees (last 30 days)
$result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE hire_date >= (CURDATE() - INTERVAL 30 DAY)");
$recentHires = $result->fetch_assoc()['count'];

// Get recent employees for display
$result = $conn->query("
    SELECT e.*, 
           e.first_name,
           e.last_name,
           d.name as department_name, 
           s.name as section_name 
    FROM employees e 
    LEFT JOIN departments d ON e.department_id = d.id 
    LEFT JOIN sections s ON e.section_id = s.id 
    ORDER BY e.created_at DESC 
    LIMIT 5
");

$recentEmployees = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentEmployees[] = $row;
    }
}

// Close connection
$conn->close();
<<<<<<< HEAD

// Include the header (which handles theme and sets up the HTML document)
include 'header.php';
?>

<title><?php echo $pageTitle; ?> - HR Management System</title>

=======
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HR Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
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
<<<<<<< HEAD
                    <li><a href="dashboard.php" class="active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a></li>
                    <li><a href="employees.php">
                        <i class="fas fa-users"></i> Employees
                    </a></li>
                    <?php if (hasPermission('hr_manager')): ?>
                    <li><a href="departments.php">
                        <i class="fas fa-building"></i> Departments
                    </a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('super_admin')): ?>
                    <li><a href="admin.php?tab=users">
                        <i class="fas fa-cog"></i> Admin
                    </a></li>
                    <?php elseif (hasPermission('hr_manager')): ?>
                    <li><a href="admin.php?tab=financial">
                        <i class="fas fa-cog"></i> Admin
                    </a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('hr_manager')): ?>
                    <li><a href="reports.php">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('hr_manager') || hasPermission('super_admin') || hasPermission('dept_head') || hasPermission('officer')): ?>
                    <li><a href="leave_management.php">
                        <i class="fas fa-calendar-alt"></i> Leave Management
                    </a></li>
                    <?php endif; ?>
                    <li><a href="strategic_plan.php?tab=strategic_plan">
                        <i class="fas fa-star"></i> Performance Management
                    </a></li>
                    <li><a href="payroll_management.php">
                        <i class="fas fa-money-check"></i> Payroll
                    </a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            
            <!-- Content -->
=======
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="employees.php">Employees</a></li>
                    <?php if (hasPermission('hr_manager')): ?>
                    <li><a href="departments.php">Departments</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('super_admin')): ?>
                   <li><a href="admin.php?tab=users">Admin</a></li>
                   <?php elseif (hasPermission('hr_manager')): ?>
                  <li><a href="admin.php?tab=financial">Admin</a></li>
                   <?php endif; ?>
                    <?php if (hasPermission('hr_manager')): ?>
                    <li><a href="reports.php">Reports</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('hr_manager')|| hasPermission('super_admin')||hasPermission('dept_head')||hasPermission('officer')): ?>
                    <li><a href="leave_management.php">Leave Management</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <button class="sidebar-toggle">☰</button>
                <h1>HR Management Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    <span class="badge badge-info"><?php echo ucwords(str_replace('_', ' ', $user['role'])); ?></span>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
            <div class="content">
                <?php $flash = getFlashMessage(); if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $totalEmployees; ?></h3>
                        <p>Active Employees</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $totalDepartments; ?></h3>
                        <p>Departments</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $totalSections; ?></h3>
                        <p>Sections</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $recentHires; ?></h3>
                        <p>New Hires (30 days)</p>
                    </div>
                </div>
                
                <div class="table-container">
                    <h3>Recent Employees</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Section</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Hire Date</th>
<<<<<<< HEAD
                                <th>Actions</th>
=======
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentEmployees)): ?>
                                <tr>
<<<<<<< HEAD
                                    <td colspan="8" class="text-center">
                                        <i class="fas fa-users fa-2x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                                        <p style="color: var(--text-muted);">No employees found</p>
                                    </td>
=======
                                    <td colspan="7" class="text-center">No employees found</td>
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentEmployees as $employee): ?>
                                <tr>
<<<<<<< HEAD
                                    <td>
                                        <strong><?php echo htmlspecialchars($employee['employee_id']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                    </td>
=======
                                    <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
                                    <td><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($employee['section_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo getEmployeeTypeBadge($employee['employee_type'] ?? ''); ?>">
                                            <?php 
                                            $type = $employee['employee_type'] ?? '';
                                            echo $type ? ucwords(str_replace('_', ' ', $type)) : 'N/A'; 
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getEmployeeStatusBadge($employee['employee_status'] ?? ''); ?>">
                                            <?php 
                                            $status = $employee['employee_status'] ?? '';
                                            echo $status ? ucwords($status) : 'N/A'; 
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($employee['hire_date']); ?></td>
<<<<<<< HEAD
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <a href="employees.php?action=view&id=<?php echo $employee['id']; ?>" 
                                               class="btn btn-sm" 
                                               style="background: linear-gradient(45deg, var(--secondary-color), #5a32a3); color: white; padding: 0.25rem 0.5rem;"
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (hasPermission('hr_manager')): ?>
                                            <a href="employees.php?action=edit&id=<?php echo $employee['id']; ?>" 
                                               class="btn btn-sm"
                                               style="background: linear-gradient(45deg, var(--warning-color), #e09900); color: white; padding: 0.25rem 0.5rem;"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
=======
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="action-buttons">
<<<<<<< HEAD
                    <a href="employees.php" class="btn btn-primary">
                        <i class="fas fa-users"></i> View All Employees
                    </a>
                    <?php if (hasPermission('hr_manager')): ?>
                        <a href="employees.php?action=add" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Add New Employee
                        </a>
                        <a href="reports.php" class="btn btn-secondary">
                            <i class="fas fa-chart-bar"></i> Generate Report
                        </a>
=======
                    <a href="employees.php" class="btn btn-primary">View All Employees</a>
                    <?php if (hasPermission('hr_manager')): ?>
                        <a href="employees.php?action=add" class="btn btn-success">Add New Employee</a>
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD
    
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
=======
>>>>>>> 86d68ff94e965ff4593e34aa4e2cc57edde6d5d3
</body>
</html>
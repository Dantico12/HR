
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';
$conn = getConnection();

// Get current user from session
$user = [
    'first_name' => isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'User',
    'last_name' => isset($_SESSION['user_name']) ? (explode(' ', $_SESSION['user_name'])[1] ?? '') : '',
    'role' => $_SESSION['user_role'] ?? 'guest',
    'id' => $_SESSION['user_id']
];

// Permission checking function
function hasPermission($required_role) {
    global $user;
    $role_hierarchy = [
        'managing_director' => 6,
        'super_admin' => 5,
        'hr_manager' => 4,
        'dept_head' => 3,
        'section_head' => 2,
        'manager' => 1,
        'employee' => 0
    ];

    $user_level = $role_hierarchy[$user['role']] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;

    return $user_level >= $required_level;
}

// Check if user has permission to access this page
if (!in_array($user['role'], ['hr_manager', 'dept_head', 'section_head', 'manager', 'managing_director', 'super_admin'])) {
    header("Location: leave_management.php");
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

function formatDate($date) {
    if (!$date) return 'N/A';
    return date('M d, Y', strtotime($date));
}

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'approved': return 'badge-success';
        case 'rejected': return 'badge-danger';
        case 'pending': return 'badge-warning';
        case 'pending_section_head': return 'badge-info';
        case 'pending_dept_head': return 'badge-primary';
        case 'pending_hr': return 'badge-warning';
        default: return 'badge-secondary';
    }
}

function getStatusDisplayName($status) {
    switch ($status) {
        case 'approved': return 'Approved';
        case 'rejected': return 'Rejected';
        case 'pending': return 'Pending';
        case 'pending_section_head': return 'Pending Section Head Approval';
        case 'pending_dept_head': return 'Pending Department Head Approval';
        case 'pending_hr': return 'Pending HR Approval';
        default: return ucfirst($status);
    }
}

// Get user's employee record
$userEmployeeQuery = "SELECT e.* FROM employees e 
                      LEFT JOIN users u ON u.employee_id = e.employee_id 
                      WHERE u.id = ?";
$stmt = $conn->prepare($userEmployeeQuery);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$userEmployee = $stmt->get_result()->fetch_assoc();

// Initialize variables
$success = '';
$error = '';
$pendingLeaves = [];
$approvedLeaves = [];
$rejectedLeaves = [];

// Handle form submissions and GET actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    // Include all the action handling logic from the original file
    // This would be the same logic from the original manage tab handling
    
    // Handle approval/rejection actions
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        if ($action === 'section_head_approve' && isset($_GET['id']) && hasPermission('section_head')) {
            $leaveId = (int)$_GET['id'];
            try {
                $conn->begin_transaction();
                
                $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
                $stmt->bind_param("i", $leaveId);
                $stmt->execute();
                $application = $stmt->get_result()->fetch_assoc();
                
                $userEmpQuery = "SELECT id FROM employees WHERE employee_id = (SELECT employee_id FROM users WHERE id = ? )";
                $stmt = $conn->prepare($userEmpQuery);
                $stmt->bind_param("s", $user['id']);
                $stmt->execute();
                $userEmpRecord = $stmt->get_result()->fetch_assoc();
                
                $empSectionQuery = "SELECT section_id FROM employees WHERE id = ?";
                $stmt = $conn->prepare($empSectionQuery);
                $stmt->bind_param("i", $application['employee_id']);
                $stmt->execute();
                $empSectionResult = $stmt->get_result();
                $empSection = $empSectionResult->fetch_assoc();
                
                if ($userEmpRecord && $application && $application['status'] === 'pending_section_head' &&
                    $empSection['section_id'] == $userEmployee['section_id']) {
                    
                    $stmt = $conn->prepare("UPDATE leave_applications SET status = 'pending_dept_head', section_head_approval = 'approved', section_head_approved_by = ?, section_head_approved_at = NOW() WHERE id = ?");
                    $stmt->bind_param("ii", $userEmpRecord['id'], $leaveId);
                    $stmt->execute();
                    
                    $conn->commit();
                    $_SESSION['flash_message'] = "Leave application approved by section head. Sent to department head.";
                    $_SESSION['flash_type'] = "success";
                } else {
                    $conn->rollback();
                    $_SESSION['flash_message'] = "You are not authorized to approve this leave application.";
                    $_SESSION['flash_type'] = "danger";
                }
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['flash_message'] = "Database error: " . $e->getMessage();
                $_SESSION['flash_type'] = "danger";
            }
            header("Location: manage.php");
            exit();
        }
        
        // Add other action handlers here (reject, dept_head_approve, etc.)
    }
}

// Fetch data for displays
try {
    // Role-specific filtering for pending leaves
    if ($user['role'] === 'section_head' && $userEmployee) {
        $sectionId = (int)$userEmployee['section_id'];
        
        $pendingQuery = "SELECT la.*, e.employee_id as emp_id, e.first_name, e.last_name,
                         lt.name as leave_type_name, d.name as department_name, s.name as section_name,
                         la.primary_days, la.annual_days, la.unpaid_days
                         FROM leave_applications la
                         JOIN employees e ON la.employee_id = e.id
                         JOIN leave_types lt ON la.leave_type_id = lt.id
                         LEFT JOIN departments d ON e.department_id = d.id
                         LEFT JOIN sections s ON e.section_id = s.id
                         WHERE la.status = 'pending_section_head'
                         AND e.section_id = ?
                         ORDER BY la.applied_at DESC";
        $stmt = $conn->prepare($pendingQuery);
        $stmt->bind_param("i", $sectionId);
        $stmt->execute();
        $pendingResult = $stmt->get_result();
        $pendingLeaves = $pendingResult->fetch_all(MYSQLI_ASSOC);
    } 
    elseif ($user['role'] === 'dept_head' && $userEmployee) {
        $deptId = (int)$userEmployee['department_id'];
        
        $pendingQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name,
                        lt.name as leave_type_name, d.name as department_name, s.name as section_name,
                        la.primary_days, la.annual_days, la.unpaid_days
                        FROM leave_applications la
                        JOIN employees e ON la.employee_id = e.id
                        JOIN leave_types lt ON la.leave_type_id = lt.id
                        LEFT JOIN departments d ON e.department_id = d.id
                        LEFT JOIN sections s ON e.section_id = s.id
                        WHERE la.status = 'pending_dept_head'
                        AND e.department_id = ?
                        ORDER BY la.applied_at DESC";
        $stmt = $conn->prepare($pendingQuery);
        $stmt->bind_param("i", $deptId);
        $stmt->execute();
        $pendingResult = $stmt->get_result();
        $pendingLeaves = $pendingResult->fetch_all(MYSQLI_ASSOC);
    }
    else {
        // HR and other roles see all pending applications
        $pendingQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name,
                         lt.name as leave_type_name, d.name as department_name, s.name as section_name,
                         la.primary_days, la.annual_days, la.unpaid_days
                         FROM leave_applications la
                         JOIN employees e ON la.employee_id = e.id
                         JOIN leave_types lt ON la.leave_type_id = lt.id
                         LEFT JOIN departments d ON e.department_id = d.id
                         LEFT JOIN sections s ON e.section_id = s.id
                         WHERE la.status IN ('pending', 'pending_section_head', 'pending_dept_head')
                         ORDER BY la.applied_at DESC";
        $pendingResult = $conn->query($pendingQuery);
        $pendingLeaves = $pendingResult->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $error = "Error fetching data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave - HR Management System</title>
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
            <div class="nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="employees.php">Employees</a></li>
                    <?php if (hasPermission('hr_manager')): ?>
                    <li><a href="departments.php">Departments</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('super_admin')|| hasPermission('hr_manager')): ?>
                    <li><a href="admin.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="leave_management.php">Leave Management</a></li>
                    <?php if (hasPermission('hr_manager')): ?>
                    <li><a href="reports.php">Reports</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Manage Leave Applications</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    <span class="badge badge-info"><?php echo ucwords(str_replace('_', ' ', $user['role'])); ?></span>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>

            <div class="content">
                <?php $flash = getFlashMessage(); if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="leave-tabs">
                    <a href="leave_management.php" class="leave-tab">Apply Leave</a>
                    <?php if (in_array($user['role'], ['hr_manager', 'dept_head', 'section_head', 'manager', 'managing_director','super_admin'])): ?>
                    <a href="manage.php" class="leave-tab active">Manage Leave</a>
                    <?php endif; ?>
                    <?php if(in_array($user['role'], ['hr_manager', 'super_admin', 'manager','managing director'])): ?>
                    <a href="history.php" class="leave-tab">Leave History</a>
                    <a href="holidays.php" class="leave-tab">Holidays</a>
                    <?php endif; ?>
                    <a href="profile.php" class="leave-tab">My Leave Profile</a>
                </div>

                <!-- Manage Leave Tab Content -->
                <div class="tab-content">
                    <h3>Manage Leave Applications</h3>

                    <!-- Pending Leaves -->
                    <div class="table-container mb-4">
                        <h4>Pending Leave Applications</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Applied Date</th>
                                    <th>Department/Section</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingLeaves)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No pending leave applications</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pendingLeaves as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['employee_id'] . ' - ' . $leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($leave['status']); ?>">
                                                <?php echo getStatusDisplayName($leave['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($leave['applied_at']); ?></td>
                                        <td><?php echo htmlspecialchars(($leave['department_name'] ?? 'N/A') . ' / ' . ($leave['section_name'] ?? 'N/A')); ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'section_head' && $leave['status'] === 'pending_section_head'): ?>
                                                <a href="manage.php?action=section_head_approve&id=<?php echo $leave['id']; ?>"
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Approve this leave application as section head?')">Approve</a>
                                                <a href="manage.php?action=section_head_reject&id=<?php echo $leave['id']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Reject this leave application?')">Reject</a>
                                            <?php elseif ($user['role'] === 'dept_head' && $leave['status'] === 'pending_dept_head'): ?>
                                                <a href="manage.php?action=dept_head_approve&id=<?php echo $leave['id']; ?>"
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Approve this leave application as department head?')">Approve</a>
                                                <a href="manage.php?action=dept_head_reject&id=<?php echo $leave['id']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Reject this leave application?')">Reject</a>
                                            <?php elseif ($user['role'] === 'managing_director' && $leave['status'] === 'pending_managing_director'): ?>
                                                <a href="manage.php?action=approve_leave&id=<?php echo $leave['id']; ?>"
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Approve this leave application as Managing Director?')">Approve</a>
                                                <a href="manage.php?action=reject_leave&id=<?php echo $leave['id']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Reject this leave application?')">Reject</a>
                                            <?php elseif ($user['role'] === 'hr_manager'): ?>
                                                <?php if ($leave['status'] === 'pending_hr_manager'): ?>
                                                    <a href="manage.php?action=approve_leave&id=<?php echo $leave['id']; ?>"
                                                       class="btn btn-success btn-sm"
                                                       onclick="return confirm('Approve this leave application?')">HR Approve</a>
                                                    <a href="manage.php?action=reject_leave&id=<?php echo $leave['id']; ?>"
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Reject this leave application?')">Reject</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Awaiting other approvals</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No actions available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Approved Leaves -->
                    <div class="table-container mb-4">
                        <h4>Recently Approved Leaves</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Approved By</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($approvedLeaves)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No approved leaves found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($approvedLeaves as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['employee_id'] . ' - ' . $leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td><?php echo htmlspecialchars($leave['approver_name'] ?? 'System'); ?></td>
                                        <td><span class="badge badge-success">Approved</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Rejected Leaves -->
                    <div class="table-container">
                        <h4>Recently Rejected Leaves</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Rejected By</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rejectedLeaves)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No rejected leaves found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rejectedLeaves as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['employee_id'] . ' - ' . $leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td><?php echo htmlspecialchars($leave['approver_name'] ?? 'System'); ?></td>
                                        <td><span class="badge badge-danger">Rejected</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

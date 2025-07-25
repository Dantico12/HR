<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start(); // Add this at the top
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

// Initialize $tab with default value BEFORE any output
$tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'apply';

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

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return false;
}

// Helper functions for leave management
function calculateBusinessDays($startDate, $endDate, $conn, $includeWeekends = false) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $days = 0;

    // Get holidays from database
    $holidayQuery = "SELECT date FROM holidays WHERE date BETWEEN ? AND ?";
    $stmt = $conn->prepare($holidayQuery);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $holidays = [];
    while ($row = $result->fetch_assoc()) {
        $holidays[] = $row['date'];
    }

    $current = clone $start;
    while ($current <= $end) {
        $dayOfWeek = $current->format('N'); // 1 = Monday, 7 = Sunday
        $currentDate = $current->format('Y-m-d');

        // Skip weekends if not included
        if (!$includeWeekends && ($dayOfWeek == 6 || $dayOfWeek == 7)) {
            $current->add(new DateInterval('P1D'));
            continue;
        }

        // Skip holidays
        if (!in_array($currentDate, $holidays)) {
            $days++;
        }

        $current->add(new DateInterval('P1D'));
    }

    return $days;
}

function getLeaveTypeBalance($employeeId, $leaveTypeId, $conn) {
    $query = "SELECT * FROM leave_balances WHERE employee_id = ? AND leave_type_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $employeeId, $leaveTypeId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row;
    }

    return ['allocated' => 0, 'used' => 0, 'remaining' => 0];
}

function updateLeaveBalance($employeeId, $leaveTypeId, $days, $conn, $action = 'use') {
    $balance = getLeaveTypeBalance($employeeId, $leaveTypeId, $conn);

    if ($action == 'use') {
        $newUsed = $balance['used'] + $days;
        $newRemaining = $balance['allocated'] - $newUsed;
    } else {
        $newUsed = max(0, $balance['used'] - $days);
        $newRemaining = $balance['allocated'] - $newUsed;
    }

    $query = "UPDATE leave_balances SET used = ?, remaining = ? WHERE employee_id = ? AND leave_type_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $newUsed, $newRemaining, $employeeId, $leaveTypeId);
    return $stmt->execute();
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input ?? '')));
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

// Get user's employee record for auto-filling
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
$employees = [];
$departments = [];
$sections = [];
$leaveTypes = [];
$leaveApplications = [];
$leaveBalances = [];
$pendingLeaves = [];
$approvedLeaves = [];
$rejectedLeaves = [];
$currentLeaves = [];
$allLeaves = [];
$holidays = [];
$employee = null;
$leaveBalance = null;
$leaveHistory = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'apply_leave':
            // Use posted employee_id, not always $userEmployee['id']
            $employeeId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : ($userEmployee['id'] ?? 0);
            $leaveTypeId = (int)$_POST['leave_type_id'];
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            $reason = sanitizeInput($_POST['reason']);
           

            // Calculate days
            $days = calculateBusinessDays($startDate, $endDate, $conn);

            // Check balance
            $balance = getLeaveTypeBalance($employeeId, $leaveTypeId, $conn);
            $warningMessage = "";
            
            // Show warning if balance is insufficient, but still allow submission
            if ($days > $balance['remaining']) {
                $warningMessage = "Warning: This leave request exceeds your available balance. You have {$balance['remaining']} days remaining.";
            }
            
            try {
                // Get the section head and department head for this employee (fix: join users for role)
                $getManagersQuery = "SELECT
                    e.section_id, e.department_id,
                    (SELECT e2.id FROM employees e2 JOIN users u2 ON u2.employee_id = e2.employee_id WHERE e2.section_id = e.section_id AND u2.role = 'section_head' LIMIT 1) as section_head_emp_id,
                    (SELECT e3.id FROM employees e3 JOIN users u3 ON u3.employee_id = e3.employee_id WHERE e3.department_id = e.department_id AND u3.role = 'dept_head' LIMIT 1) as dept_head_emp_id
                    FROM employees e WHERE e.id = ?";
                $stmt = $conn->prepare($getManagersQuery);
                $stmt->bind_param("i", $employeeId);
                $stmt->execute();
                $managersResult = $stmt->get_result();
                $managers = $managersResult->fetch_assoc();
                
                $sectionHeadEmpId = $managers['section_head_emp_id'] ?? null;
                $deptHeadEmpId = $managers['dept_head_emp_id'] ?? null;
                
                // Set initial status to pending_section_head for multi-level approval
                $initialStatus = 'pending_section_head';
                
                // Debug the section head and department head IDs
                echo '<div class="alert alert-info">Debug: Setting section_head_emp_id=' . $sectionHeadEmpId . ', dept_head_emp_id=' . $deptHeadEmpId . '</div>';
                $stmt = $conn->prepare("INSERT INTO leave_applications
                    (employee_id, leave_type_id, start_date, end_date, days_requested, reason,
                     status, applied_at, section_head_emp_id, dept_head_emp_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
                $stmt->bind_param("iisssssii", $employeeId, $leaveTypeId, $startDate, $endDate,
                                $days, $reason, $initialStatus, $sectionHeadEmpId, $deptHeadEmpId);

                if ($stmt->execute()) {
                    $success = $warningMessage ? $warningMessage . " Leave application submitted successfully!" : "Leave application submitted successfully!";
                } else {
                    $error = "Error submitting application.";
                }
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
            break;

        case 'approve_leave':
            if (hasPermission('hr_manager')) {
                $applicationId = (int)$_POST['application_id'];
                $approverComments = sanitizeInput($_POST['approver_comments']);

                try {
                    $conn->begin_transaction();

                    // Get application details
                    $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
                    $stmt->bind_param("i", $applicationId);
                    $stmt->execute();
                    $application = $stmt->get_result()->fetch_assoc();

                    // Update application status
                    $stmt = $conn->prepare("UPDATE leave_applications 
                                          SET status = 'approved', approver_id = ?, approver_comments = ?, 
                                              approved_date = NOW() WHERE id = ?");
                    $stmt->bind_param("isi", $user['id'], $approverComments, $applicationId);
                    $stmt->execute();

                    // Update leave balance
                    updateLeaveBalance($application['employee_id'], $application['leave_type_id'], 
                                     $application['days_requested'], $conn, 'use');

                    $conn->commit();
                    $success = "Leave application approved successfully!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Error approving leave: " . $e->getMessage();
                }
            }
            break;

        case 'reject_leave':
            if (hasPermission('hr_manager')) {
                $applicationId = (int)$_POST['application_id'];
                $approverComments = sanitizeInput($_POST['approver_comments']);

                try {
                    $stmt = $conn->prepare("UPDATE leave_applications 
                                          SET status = 'rejected', approver_id = ?, approver_comments = ?, 
                                              approved_date = NOW() WHERE id = ?");
                    $stmt->bind_param("isi", $user['id'], $approverComments, $applicationId);

                    if ($stmt->execute()) {
                        $success = "Leave application rejected.";
                    } else {
                        $error = "Error rejecting application.";
                    }
                } catch (Exception $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            }
            break;

        case 'add_holiday':
            if (hasPermission('hr_manager')) {
                $name = sanitizeInput($_POST['name']);
                $date = $_POST['date'];
                $description = sanitizeInput($_POST['description']);
                $isRecurring = isset($_POST['is_recurring']) ? 1 : 0;

                try {
                    $stmt = $conn->prepare("INSERT INTO holidays (name, date, description, is_recurring) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $name, $date, $description, $isRecurring);

                    if ($stmt->execute()) {
                        $success = "Holiday added successfully!";
                    } else {
                        $error = "Error adding holiday.";
                    }
                } catch (Exception $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            }
            break;
    }
}

// Handle GET actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'approve_leave' && isset($_GET['id']) && hasPermission('hr_manager')) {
        $leaveId = (int)$_GET['id'];
        try {
            $stmt = $conn->prepare("UPDATE leave_applications SET status = 'approved', approver_id = ?, approved_date = NOW() WHERE id = ?");
            $stmt->bind_param("si", $user['id'], $leaveId);

            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Leave application approved successfully!";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Error approving leave application.";
                $_SESSION['flash_type'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Database error: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }

        header("Location: leave_management.php?tab=manage");
        exit();
    }

    if ($action === 'reject_leave' && isset($_GET['id']) && hasPermission('hr_manager')) {
        $leaveId = (int)$_GET['id'];
        try {
            $stmt = $conn->prepare("UPDATE leave_applications SET status = 'rejected', approver_id = ?, approved_date = NOW() WHERE id = ?");
            $stmt->bind_param("si", $user['id'], $leaveId);

            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Leave application rejected!";
                $_SESSION['flash_type'] = "warning";
            } else {
                $_SESSION['flash_message'] = "Error rejecting leave application.";
                $_SESSION['flash_type'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Database error: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }

        header("Location: leave_management.php?tab=manage");
        exit();
    }
}

// Fetch data for dropdowns and displays
try {
    // Get departments
    $departmentsResult = $conn->query("SELECT * FROM departments ORDER BY name");
    $departments = $departmentsResult->fetch_all(MYSQLI_ASSOC);

    // Get sections
    $sectionsResult = $conn->query("SELECT s.*, d.name as department_name FROM sections s 
                                   LEFT JOIN departments d ON s.department_id = d.id ORDER BY s.name");
    $sections = $sectionsResult->fetch_all(MYSQLI_ASSOC);

    // Get leave types
    $leaveTypesResult = $conn->query("SELECT * FROM leave_types ORDER BY name");
    $leaveTypes = $leaveTypesResult->fetch_all(MYSQLI_ASSOC);

    // Get employees (for managers)
if (in_array($user['role'], ['hr_manager', 'dept_head', 'section_head'])) {
    $employeesQuery = "SELECT e.*, d.name as department_name, s.name as section_name 
                      FROM employees e 
                      LEFT JOIN departments d ON e.department_id = d.id 
                      LEFT JOIN sections s ON e.section_id = s.id";
    
    // Add role-specific filtering
    if ($user['role'] === 'dept_head') {
        $employeesQuery .= " WHERE e.department_id = " . (int)$userEmployee['department_id'];
    } 
    elseif ($user['role'] === 'section_head') {
        $employeesQuery .= " WHERE e.section_id = " . (int)$userEmployee['section_id'];
    }
    
    $employeesQuery .= " ORDER BY e.first_name, e.last_name";
    $employees = $conn->query($employeesQuery)->fetch_all(MYSQLI_ASSOC);
}

    // Get holidays
    $holidaysResult = $conn->query("SELECT * FROM holidays ORDER BY date DESC");
    $holidays = $holidaysResult->fetch_all(MYSQLI_ASSOC);

    // Prepare data based on tab
    if ($tab === 'manage' && in_array($user['role'], ['hr_manager', 'dept_head', 'section_head', 'manager', 'managing_director'])) {
        // Section head: only see leave applications for their section (pending_section_head) and their employees
        if ($user['role'] === 'section_head' && $userEmployee) {
            $sectionId = (int)$userEmployee['section_id'];
            // DEBUG OUTPUT FOR SECTION HEAD
            echo '<div class="alert alert-info" style="margin-bottom:10px;">';
            echo 'DEBUG: Logged in as Section Head: <strong>' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</strong> | Section ID: <strong>' . $sectionId . '</strong> | Employee ID: <strong>' . $userEmployee['id'] . '</strong><br>';
            
            // Count all pending applications
            $allPendingQuery = "SELECT COUNT(*) as cnt FROM leave_applications WHERE status = 'pending_section_head'";
            $allPendingResult = $conn->query($allPendingQuery);
            $allPendingRow = $allPendingResult->fetch_assoc();
            echo 'All pending section head applications in system: <strong>' . ($allPendingRow['cnt'] ?? 0) . '</strong><br>';
            
            // List all pending applications with details
            $listPendingQuery = "SELECT la.id, la.employee_id, la.section_head_emp_id, e.section_id, e.first_name, e.last_name
                                FROM leave_applications la
                                JOIN employees e ON la.employee_id = e.id
                                WHERE la.status = 'pending_section_head'";
            $listPendingResult = $conn->query($listPendingQuery);
            if ($listPendingResult->num_rows > 0) {
                echo '<strong>Pending Applications Details:</strong><br>';
                echo '<table class="table table-sm" style="font-size: 12px;">';
                echo '<tr><th>ID</th><th>Employee</th><th>Section ID</th><th>Section Head ID</th></tr>';
                while ($row = $listPendingResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
                    echo '<td>' . $row['section_id'] . '</td>';
                    echo '<td>' . $row['section_head_emp_id'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            
            // Count pending applications for this section
            $debugCountQuery = "SELECT COUNT(*) as cnt FROM leave_applications la JOIN employees e ON la.employee_id = e.id WHERE la.status = 'pending_section_head' AND (e.section_id = ? OR la.section_head_emp_id = ?)";
            $debugStmt = $conn->prepare($debugCountQuery);
            $debugStmt->bind_param("ii", $sectionId, $userEmployee['id']);
            $debugStmt->execute();
            $debugResult = $debugStmt->get_result();
            $debugRow = $debugResult->fetch_assoc();
            echo 'Pending applications for your section: <strong>' . ($debugRow['cnt'] ?? 0) . '</strong>';
            echo '</div>';

            // Get the current user's employee ID
            $userEmpId = $userEmployee['id'];
            
            // Pending applications for employees in this section
            $pendingQuery = "SELECT la.*, e.employee_id as emp_id, e.first_name, e.last_name,
                             lt.name as leave_type_name, d.name as department_name, s.name as section_name
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
            
            // Add debug info to each leave
            foreach ($pendingLeaves as &$leave) {
                $leave['debug_info'] = "Section ID: {$leave['id']}, Section Head ID: {$leave['section_head_emp_id']}";
            }

            // Approved leaves for employees in this section
            $approvedQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name, lt.name as leave_type_name
                              FROM leave_applications la
                              JOIN employees e ON la.employee_id = e.id
                              JOIN leave_types lt ON la.leave_type_id = lt.id
                              WHERE la.status = 'approved' AND e.section_id = ?
                              ORDER BY la.applied_at DESC
                              LIMIT 20";
            $stmt = $conn->prepare($approvedQuery);
            $stmt->bind_param("i", $sectionId);
            $stmt->execute();
            $approvedResult = $stmt->get_result();
            $approvedLeaves = $approvedResult->fetch_all(MYSQLI_ASSOC);

            // Rejected leaves for employees in this section
            $rejectedQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name, lt.name as leave_type_name
                              FROM leave_applications la
                              JOIN employees e ON la.employee_id = e.id
                              JOIN leave_types lt ON la.leave_type_id = lt.id
                              WHERE la.status = 'rejected' AND e.section_id = ?
                              ORDER BY la.applied_at DESC
                              LIMIT 20";
            $stmt = $conn->prepare($rejectedQuery);
            $stmt->bind_param("i", $sectionId);
            $stmt->execute();
            $rejectedResult = $stmt->get_result();
            $rejectedLeaves = $rejectedResult->fetch_all(MYSQLI_ASSOC);
        }
        // Department head: only see leave applications pending their approval
        else if ($user['role'] === 'dept_head' && $userEmployee) {
            $deptHeadEmpId = $userEmployee['id'];
            $deptId = (int)$userEmployee['department_id'];
            
            // DEBUG OUTPUT FOR DEPARTMENT HEAD
            echo '<div class="alert alert-info" style="margin-bottom:10px;">';
            echo 'DEBUG: Logged in as Department Head: <strong>' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</strong> | Department ID: <strong>' . $deptId . '</strong><br>';
            // Count pending applications for this department
            $debugCountQuery = "SELECT COUNT(*) as cnt FROM leave_applications la JOIN employees e ON la.employee_id = e.id WHERE la.status = 'pending_dept_head' AND (e.department_id = ? OR la.dept_head_emp_id = ?)";
            $debugStmt = $conn->prepare($debugCountQuery);
            $debugStmt->bind_param("ii", $deptId, $deptHeadEmpId);
            $debugStmt->execute();
            $debugResult = $debugStmt->get_result();
            $debugRow = $debugResult->fetch_assoc();
            echo 'Pending applications for your department: <strong>' . ($debugRow['cnt'] ?? 0) . '</strong>';
            echo '</div>';

            // Pending applications for employees in this department
            $pendingQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name,
                            lt.name as leave_type_name, d.name as department_name, s.name as section_name
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

            // Approved leaves for employees in this department
            $approvedQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name, lt.name as leave_type_name
                            FROM leave_applications la
                            JOIN employees e ON la.employee_id = e.id
                            JOIN leave_types lt ON la.leave_type_id = lt.id
                            WHERE la.status = 'approved' AND e.department_id = ?
                            ORDER BY la.applied_at DESC
                            LIMIT 20";
            $stmt = $conn->prepare($approvedQuery);
            $stmt->bind_param("i", $deptId);
            $stmt->execute();
            $approvedResult = $stmt->get_result();
            $approvedLeaves = $approvedResult->fetch_all(MYSQLI_ASSOC);

            // Rejected leaves for employees in this department
            $rejectedQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name, lt.name as leave_type_name
                            FROM leave_applications la
                            JOIN employees e ON la.employee_id = e.id
                            JOIN leave_types lt ON la.leave_type_id = lt.id
                            WHERE la.status = 'rejected' AND e.department_id = ?
                            ORDER BY la.applied_at DESC
                            LIMIT 20";
            $stmt = $conn->prepare($rejectedQuery);
            $stmt->bind_param("i", $deptId);
            $stmt->execute();
            $rejectedResult = $stmt->get_result();
            $rejectedLeaves = $rejectedResult->fetch_all(MYSQLI_ASSOC);
        }
        else {
            // HR and other roles: see all pending applications (not filtered by section or department)
            $pendingQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name,
                             lt.name as leave_type_name, d.name as department_name, s.name as section_name
                             FROM leave_applications la
                             JOIN employees e ON la.employee_id = e.id
                             JOIN leave_types lt ON la.leave_type_id = lt.id
                             LEFT JOIN departments d ON e.department_id = d.id
                             LEFT JOIN sections s ON e.section_id = s.id
                             WHERE la.status IN ('pending', 'pending_section_head', 'pending_dept_head')
                             ORDER BY la.applied_at DESC";
            $pendingResult = $conn->query($pendingQuery);
            $pendingLeaves = $pendingResult->fetch_all(MYSQLI_ASSOC);

            // Get approved leaves
            $approvedQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name, lt.name as leave_type_name
                              FROM leave_applications la
                              JOIN employees e ON la.employee_id = e.id
                              JOIN leave_types lt ON la.leave_type_id = lt.id
                              WHERE la.status = 'approved'
                              ORDER BY la.applied_at DESC
                              LIMIT 20";
            $approvedResult = $conn->query($approvedQuery);
            $approvedLeaves = $approvedResult->fetch_all(MYSQLI_ASSOC);

            // Get rejected leaves
            $rejectedQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name, lt.name as leave_type_name
                              FROM leave_applications la
                              JOIN employees e ON la.employee_id = e.id
                              JOIN leave_types lt ON la.leave_type_id = lt.id
                              WHERE la.status = 'rejected'
                              ORDER BY la.applied_at DESC
                              LIMIT 20";
            $rejectedResult = $conn->query($rejectedQuery);
            $rejectedLeaves = $rejectedResult->fetch_all(MYSQLI_ASSOC);
        }
    }

    if ($tab === 'history' && hasPermission('hr_manager')) {
        // Get current leaves
        $currentQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name, lt.name as leave_type_name
                         FROM leave_applications la
                         JOIN employees e ON la.employee_id = e.id
                         JOIN leave_types lt ON la.leave_type_id = lt.id
                         WHERE la.start_date <= CURDATE() AND la.end_date >= CURDATE() AND la.status = 'approved'
                         ORDER BY la.start_date";
        $currentResult = $conn->query($currentQuery);
        $currentLeaves = $currentResult->fetch_all(MYSQLI_ASSOC);

        // Get all leaves
        $allQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name, lt.name as leave_type_name
                     FROM leave_applications la
                     JOIN employees e ON la.employee_id = e.id
                     JOIN leave_types lt ON la.leave_type_id = lt.id
                     ORDER BY la.applied_at DESC
                     LIMIT 50";
        $allResult = $conn->query($allQuery);
        $allLeaves = $allResult->fetch_all(MYSQLI_ASSOC);
    }

    if ($tab === 'profile') {
        // Get employee info
        if ($userEmployee) {
            $employee = $userEmployee;

            // Get leave balance
            $balanceQuery = "SELECT * FROM leave_balances WHERE employee_id = ? ORDER BY leave_type_id";
            $stmt = $conn->prepare($balanceQuery);
            $stmt->bind_param("i", $employee['id']);
            $stmt->execute();
            $balanceResult = $stmt->get_result();
            $leaveBalance = $balanceResult->fetch_assoc();

            // Get leave history
            $historyQuery = "SELECT la.*, lt.name as leave_type_name
                             FROM leave_applications la
                             JOIN leave_types lt ON la.leave_type_id = lt.id
                             WHERE la.employee_id = ?
                             ORDER BY la.applied_at DESC";
            $stmt = $conn->prepare($historyQuery);
            $stmt->bind_param("i", $employee['id']);
            $stmt->execute();
            $historyResult = $stmt->get_result();
            $leaveHistory = $historyResult->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Get leave applications
    if (hasPermission('hr_manager')) {
        // Managers can see all applications
        $applicationsQuery = "SELECT la.*, e.employee_id, e.first_name, e.last_name, 
                             lt.name as leave_type_name, d.name as department_name, s.name as section_name,
                             u.first_name as approver_first_name, u.last_name as approver_last_name
                             FROM leave_applications la
                             JOIN employees e ON la.employee_id = e.id
                             JOIN leave_types lt ON la.leave_type_id = lt.id
                             LEFT JOIN departments d ON e.department_id = d.id
                             LEFT JOIN sections s ON e.section_id = s.id
                             LEFT JOIN users u ON la.approver_id = u.id
                             ORDER BY la.applied_at DESC";
        $applicationsResult = $conn->query($applicationsQuery);
        $leaveApplications = $applicationsResult->fetch_all(MYSQLI_ASSOC);
    } else {
        // Regular employees see only their applications
        if ($userEmployee) {
            $stmt = $conn->prepare("SELECT la.*, lt.name as leave_type_name,
                                   u.first_name as approver_first_name, u.last_name as approver_last_name
                                   FROM leave_applications la
                                   JOIN leave_types lt ON la.leave_type_id = lt.id
                                   LEFT JOIN users u ON la.approver_id = u.id
                                   WHERE la.employee_id = ?
                                   ORDER BY la.applied_at DESC");
            $stmt->bind_param("i", $userEmployee['id']);
            $stmt->execute();
            $leaveApplications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Get leave balances for current user
    if ($userEmployee) {
        $stmt = $conn->prepare("SELECT lb.*, lt.name as leave_type_name 
                               FROM leave_balances lb
                               JOIN leave_types lt ON lb.leave_type_id = lt.id
                               WHERE lb.employee_id = ?
                               ORDER BY lt.name");
        $stmt->bind_param("i", $userEmployee['id']);
        $stmt->execute();
        $leaveBalances = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
    <title>Leave Management - HR Management System</title>
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
                    <li><a href="leave_management.php" class="active">Leave Management</a></li>
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
                <h1>Leave Management System</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
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
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="leave-tabs">
                    <a href="leave_management.php?tab=apply" class="leave-tab <?php echo $tab === 'apply' ? 'active' : ''; ?>">Apply Leave</a>
                <?php if (in_array($user['role'], ['hr_manager', 'dept_head', 'section_head', 'manager', 'managing_director'])): ?>
                <a href="leave_management.php?tab=manage" class="leave-tab <?php echo $tab === 'manage' ? 'active' : ''; ?>">Manage Leave</a>
                <a href="leave_management.php?tab=history" class="leave-tab <?php echo $tab === 'history' ? 'active' : ''; ?>">Leave History</a>
                <a href="leave_management.php?tab=holidays" class="leave-tab <?php echo $tab === 'holidays' ? 'active' : ''; ?>">Holidays</a>
                <?php endif; ?>
                    <a href="leave_management.php?tab=profile" class="leave-tab <?php echo $tab === 'profile' ? 'active' : ''; ?>">My Leave Profile</a>
                </div>

                <?php if ($tab === 'apply'): ?>
                <!-- Apply Leave Tab -->
                <div class="tab-content">
                    <h3>Apply for Leave</h3>

                    <?php if ($userEmployee): ?>
                    <form method="POST" action="">
                    <input type="hidden" name="action" value="apply_leave">
                        <div class="form-grid">
                        <div class="form-group">
    <label for="employee_id">Employee</label>
    <select id="employee_id" name="employee_id" class="form-control" required>
        <option value="">Select Employee</option>
        <?php 
        if ($userEmployee) {
            echo '<!-- DEBUG: Current User - Role: ' . $user['role'] . 
                 ' | Dept: ' . ($userEmployee['department_id'] ?? 'NULL') . 
                 ' | Section: ' . ($userEmployee['section_id'] ?? 'NULL') . ' -->';

            // Regular Employee: Only show their own name
            if (!in_array($user['role'], ['hr_manager', 'dept_head', 'section_head'])) {
                echo '<option value="' . $userEmployee['id'] . '" selected>' . 
                     htmlspecialchars(
                         $userEmployee['employee_id'] . ' - ' . 
                         $userEmployee['first_name'] . ' ' . 
                         $userEmployee['last_name'] . ' (' . 
                         ($userEmployee['designation'] ?? '') . ')'
                     ) . '</option>';
            } 
            // Managers: Show filtered lists
            elseif (isset($employees) && is_array($employees)) {
                foreach ($employees as $employee) {
                    $selected = ($employee['id'] == $userEmployee['id']) ? 'selected' : '';
                    echo '<option value="' . $employee['id'] . '" ' . $selected . '>' . 
                         htmlspecialchars(
                             $employee['employee_id'] . ' - ' . 
                             $employee['first_name'] . ' ' . 
                             $employee['last_name'] . ' (' . 
                             ($employee['designation'] ?? '') . ')'
                         ) . '</option>';
                }
            }
        } else {
            echo '<option value="">No employee record found</option>';
        }
        ?>
    </select>
</div>
                            <div class="form-group">
                                <label for="leave_type_id">Leave Type</label>
                                <select name="leave_type_id" id="leave_type_id" class="form-control" required>
                                    <option value="">Select Leave Type</option>
                                    <?php foreach ($leaveTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="calculated_days">Calculated Days</label>
                                <input type="text" id="calculated_days" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason for Leave</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                        </div>

                        

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Application</button>
                            <button type="reset" class="btn btn-secondary">Reset Form</button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        Your user account is not linked to an employee record. Please contact HR to resolve this issue.
                    </div>
                    <?php endif; ?>

                    <!-- My Leave Applications -->
                    <div class="table-container mt-4">
                        <h3>My Leave Applications</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Applied Date</th>
                                    <th>Approver</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($leaveApplications)): ?>
                                    <?php foreach ($leaveApplications as $application): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($application['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($application['start_date']); ?></td>
                                        <td><?php echo formatDate($application['end_date']); ?></td>
                                        <td><?php echo $application['days_requested']; ?></td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($application['status']); ?>">
                                                <?php echo getStatusDisplayName($application['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($application['applied_at']); ?></td>
                                        <td>
                                            <?php 
                                            if ($application['approver_first_name']) {
                                                echo htmlspecialchars($application['approver_first_name'] . ' ' . $application['approver_last_name']);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No leave applications found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php elseif ($tab === 'manage' && in_array($user['role'], ['hr_manager', 'dept_head', 'section_head', 'manager', 'managing_director'])): ?>
                <!-- Manage Leave Tab -->
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
                                    <th>Applied Date</th>
                                    <th>Department/Section</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingLeaves)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No pending leave applications</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pendingLeaves as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['employee_id'] . ' - ' . $leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td><?php echo formatDate($leave['applied_at']); ?></td>
                                        <td><?php echo htmlspecialchars(($leave['department_name'] ?? 'N/A') . ' / ' . ($leave['section_name'] ?? 'N/A')); ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'section_head' && $leave['status'] === 'pending_section_head'): ?>
                                                <a href="leave_management.php?action=section_head_approve&id=<?php echo $leave['id']; ?>&tab=manage"
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Approve this leave application as section head?')">Section Head Approve</a>
                                                <a href="leave_management.php?action=section_head_reject&id=<?php echo $leave['id']; ?>&tab=manage"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Reject this leave application?')">Reject</a>
                                            <?php elseif ($user['role'] === 'dept_head' && $leave['status'] === 'pending_dept_head'): ?>
                                                <a href="leave_management.php?action=dept_head_approve&id=<?php echo $leave['id']; ?>&tab=manage"
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Approve this leave application as department head?')">Dept Head Approve</a>
                                                <a href="leave_management.php?action=dept_head_reject&id=<?php echo $leave['id']; ?>&tab=manage"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Reject this leave application?')">Reject</a>
                                            <?php elseif ($user['role'] === 'hr_manager'): ?>
                                                <a href="leave_management.php?action=approve_leave&id=<?php echo $leave['id']; ?>&tab=manage"
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Approve this leave application?')">HR Approve</a>
                                                <a href="leave_management.php?action=reject_leave&id=<?php echo $leave['id']; ?>&tab=manage"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Reject this leave application?')">Reject</a>
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
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($approvedLeaves)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No approved leaves found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($approvedLeaves as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['employee_id'] . ' - ' . $leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
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
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rejectedLeaves)): ?>
                                    <tr>
                                        <td colspan="6"class="text-center">No rejected leaves found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rejectedLeaves as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['employee_id'] . ' - ' . $leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td><span class="badge badge-danger">Rejected</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php elseif ($tab === 'history' && hasPermission('hr_manager')): ?>
                <!-- Leave History Tab -->
                <div class="tab-content">
                    <h3>Leave History</h3>

                    <!-- Employees Currently on Leave -->
                    <div class="table-container mb-4">
                        <h4>Employees Currently on Leave</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Remaining Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($currentLeaves)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No employees currently on leave</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($currentLeaves as $leave): ?>
                                    <?php
                                        $today = new DateTime();
                                        $endDate = new DateTime($leave['end_date']);
                                        $remainingDays = $today->diff($endDate)->days;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['employee_id'] . ' - ' . $leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td><?php echo $remainingDays; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- All Leave History -->
                    <div class="table-container">
                        <h4>All Leave Applications (Recent 50)</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allLeaves as $leave): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($leave['employee_id'] . ' - ' . $leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                    <td><?php echo formatDate($leave['start_date']); ?></td>
                                    <td><?php echo formatDate($leave['end_date']); ?></td>
                                    <td><?php echo $leave['days_requested']; ?></td>
                                    <td><?php echo formatDate($leave['applied_at']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'badge-warning',
                                            'approved' => 'badge-success',
                                            'rejected' => 'badge-danger',
                                            'cancelled' => 'badge-secondary'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $statusClass[$leave['status']] ?? 'badge-light'; ?>">
                                            <?php echo ucfirst($leave['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php elseif ($tab === 'holidays' && hasPermission('hr_manager')): ?>
                <!-- Holidays Management Content -->
                <div class="tab-content">
                    <h3>Manage Holidays</h3>
                    <form method="POST" action="" class="mb-4">
                        <input type="hidden" name="action" value="add_holiday">
                        <h4>Add New Holiday</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Holiday Name</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="date" id="date" name="date" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_recurring"> This is a recurring holiday
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Holiday</button>
                    </form>

                    <div class="table-container">
                        <h4>Current Holidays</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Recurring</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($holidays)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No holidays found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($holidays as $holiday): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($holiday['name']); ?></td>
                                        <td><?php echo formatDate($holiday['date']); ?></td>
                                        <td><?php echo htmlspecialchars($holiday['description'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $holiday['is_recurring'] ? 'badge-success' : 'badge-secondary'; ?>">
                                                <?php echo $holiday['is_recurring'] ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="leave_management.php?action=delete_holiday&id=<?php echo $holiday['id']; ?>&tab=holidays" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to delete this holiday?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php elseif ($tab === 'profile'): ?>
                <!-- My Leave Profile Tab -->
                <div class="tab-content">
                    <h3>My Leave Profile</h3>

                    <?php if ($employee): ?>
                    <!-- Employee Information -->
                    <div class="employee-info mb-4">
                        <div class="form-grid">
                            <div>
                                <h4>Employee Information</h4>
                                <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($employee['employee_id']); ?></p>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></p>
                                <p><strong>Employment Type:</strong> <?php echo htmlspecialchars($employee['employment_type']); ?></p>
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['department_id'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <h4>Leave Balance (Current Year)</h4>
                                <?php if ($leaveBalance): ?>
                                    <p><strong>Annual Leave Entitled:</strong> <?php echo $leaveBalance['allocated'] ?? 0; ?> days</p>
                                    <p><strong>Annual Leave Used:</strong> <?php echo $leaveBalance['used'] ?? 0; ?> days</p>
                                    <p><strong>Annual Leave Balance:</strong> <span class="badge badge-info"><?php echo $leaveBalance['remaining'] ?? 0; ?> days</span></p>
                                <?php else: ?>
                                    <p class="text-muted">Leave balance not available. Please contact HR.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Leave History -->
                    <div class="table-container">
                        <h4>My Leave History</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($leaveHistory)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No leave applications found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($leaveHistory as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td><?php echo formatDate($leave['applied_at']); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'badge-warning',
                                                'approved' => 'badge-success',
                                                'rejected' => 'badge-danger',
                                                'cancelled' => 'badge-secondary'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $statusClass[$leave['status']] ?? 'badge-light'; ?>">
                                                <?php echo ucfirst($leave['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($leave['reason'], 0, 50) . (strlen($leave['reason']) > 50 ? '...' : '')); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Quick Actions -->
                    <div class="action-buttons mt-4">
                        <a href="leave_management.php?tab=apply" class="btn btn-primary">Apply for New Leave</a>
                    </div>

                    <?php else: ?>
                    <div class="alert alert-warning">
                        Employee record not found. Please contact HR to resolve this issue.
                    </div>
                    <?php endif; ?>
                </div>

                <?php else: ?>
                <div class="tab-content">
                    <p>Access denied or content under development.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

        <script>
        // Updated JavaScript calculation
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const leaveTypeInput = document.getElementById('leave_type_id');
            const calculatedDays = document.getElementById('calculated_days');

            function calculateDays() {
                if (startDateInput.value && endDateInput.value) {
                    const start = new Date(startDateInput.value);
                    const end = new Date(endDateInput.value);
                    const leaveTypeId = leaveTypeInput.value;
                    
                    if (end >= start) {
                        let diffDays = 0;
                        let current = new Date(start);
                        
                        // Check if maternity leave (you'll need to set this based on your leave types)
                        const isMaternity = (leaveTypeId === '3'); // Assuming 3 is maternity leave ID
                        
                        while (current <= end) {
                            const dayOfWeek = current.getDay(); // 0 = Sunday, 6 = Saturday
                            
                            // Count weekends only for maternity leave
                            if (isMaternity || (dayOfWeek !== 0 && dayOfWeek !== 6)) {
                                diffDays++;
                            }
                            
                            current.setDate(current.getDate() + 1);
                        }
                        
                        calculatedDays.value = diffDays + ' days';
                    } else {
                        calculatedDays.value = 'Invalid date range';
                    }
                } else {
                    calculatedDays.value = '';
                }
            }

            // Event listeners
            startDateInput.addEventListener('change', calculateDays);
            endDateInput.addEventListener('change', calculateDays);
            leaveTypeInput.addEventListener('change', calculateDays);

            // Set minimum date to today for leave applications
            const today = new Date().toISOString().split('T')[0];
            if (startDateInput) {
                startDateInput.min = today;
            }
            if (endDateInput) {
                endDateInput.min = today;
            }
        });
    </script>

</body>
</html>
<?php
//Get the leave_management_handler


// Handle GET actions for department head approval/rejection
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    // Section Head Approval
    if ($action === 'section_head_approve' && isset($_GET['id']) && hasPermission('section_head')) {
        $leaveId = (int)$_GET['id'];
        try {
            $conn->begin_transaction();
            // Get application details
            $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
            $stmt->bind_param("i", $leaveId);
            $stmt->execute();
            $application = $stmt->get_result()->fetch_assoc();
            // Get current user's employee record
            $userEmpQuery = "SELECT id FROM employees WHERE employee_id = (SELECT employee_id FROM users WHERE id = ? )";
            $stmt = $conn->prepare($userEmpQuery);
            $stmt->bind_param("s", $user['id']);
            $stmt->execute();
            $userEmpRecord = $stmt->get_result()->fetch_assoc();
            // Debug output
            echo '<div class="alert alert-info">Debug: User Emp ID=' . $userEmpRecord['id'] . ', Application section_head_emp_id=' . $application['section_head_emp_id'] . ', Status=' . $application['status'] . '</div>';
            
            // Get employee's section ID
            $empSectionQuery = "SELECT section_id FROM employees WHERE id = ?";
            $stmt = $conn->prepare($empSectionQuery);
            $stmt->bind_param("i", $application['employee_id']);
            $stmt->execute();
            $empSectionResult = $stmt->get_result();
            $empSection = $empSectionResult->fetch_assoc();
            
            // Allow section head to approve application if employee is in their section
            if ($userEmpRecord && $application && $application['status'] === 'pending_section_head' &&
                $empSection['section_id'] == $userEmployee['section_id']) {
                // Update application status to pending_dept_head
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
        header("Location: leave_management.php?tab=manage");
        exit();
    }
    
    // Section Head Reject
    if ($action === 'section_head_reject' && isset($_GET['id']) && hasPermission('section_head')) {
        $leaveId = (int)$_GET['id'];
        try {
            $conn->begin_transaction();
            // Get application details
            $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
            $stmt->bind_param("i", $leaveId);
            $stmt->execute();
            $application = $stmt->get_result()->fetch_assoc();
            // Get current user's employee record
            $userEmpQuery = "SELECT id FROM employees WHERE employee_id = (SELECT employee_id FROM users WHERE id = ? )";
            $stmt = $conn->prepare($userEmpQuery);
            $stmt->bind_param("s", $user['id']);
            $stmt->execute();
            $userEmpRecord = $stmt->get_result()->fetch_assoc();
            // Get employee's section ID
            $empSectionQuery = "SELECT section_id FROM employees WHERE id = ?";
            $stmt = $conn->prepare($empSectionQuery);
            $stmt->bind_param("i", $application['employee_id']);
            $stmt->execute();
            $empSectionResult = $stmt->get_result();
            $empSection = $empSectionResult->fetch_assoc();
            
            // Allow section head to reject application if employee is in their section
            if ($userEmpRecord && $application && $application['status'] === 'pending_section_head' &&
                $empSection['section_id'] == $userEmployee['section_id']) {
                // Update application status to rejected
                $stmt = $conn->prepare("UPDATE leave_applications SET status = 'rejected', section_head_approval = 'rejected', section_head_approved_by = ?, section_head_approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $userEmpRecord['id'], $leaveId);
                $stmt->execute();
                $conn->commit();
                $_SESSION['flash_message'] = "Leave application rejected by section head.";
                $_SESSION['flash_type'] = "warning";
            } else {
                $conn->rollback();
                $_SESSION['flash_message'] = "You are not authorized to reject this leave application.";
                $_SESSION['flash_type'] = "danger";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash_message'] = "Database error: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }
        header("Location: leave_management.php?tab=manage");
        exit();
    }

    if ($action === 'dept_head_approve' && isset($_GET['id']) && hasPermission('dept_head')) {
        $leaveId = (int)$_GET['id'];

        try {
            $conn->begin_transaction();

            // Get application details
            $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
            $stmt->bind_param("i", $leaveId);
            $stmt->execute();
            $application = $stmt->get_result()->fetch_assoc();

            // Get current user's employee record
            $userEmpQuery = "SELECT id FROM employees WHERE employee_id = (SELECT employee_id FROM users WHERE id = ?)";
            $stmt = $conn->prepare($userEmpQuery);
            $stmt->bind_param("s", $user['id']);
            $stmt->execute();
            $userEmpRecord = $stmt->get_result()->fetch_assoc();

            // Get employee's department ID
            $empDeptQuery = "SELECT department_id FROM employees WHERE id = ?";
            $stmt = $conn->prepare($empDeptQuery);
            $stmt->bind_param("i", $application['employee_id']);
            $stmt->execute();
            $empDeptResult = $stmt->get_result();
            $empDept = $empDeptResult->fetch_assoc();
            
            // Allow department head to approve application if employee is in their department
            if ($userEmpRecord && $application && $application['status'] === 'pending_dept_head' &&
                $empDept['department_id'] == $userEmployee['department_id']) {
                // Update application status to approved
                $stmt = $conn->prepare("UPDATE leave_applications SET status = 'approved', dept_head_approval = 'approved', dept_head_approved_by = ?, dept_head_approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $userEmpRecord['id'], $leaveId);
                $stmt->execute();
                
                // Update leave balance
                updateLeaveBalance($application['employee_id'], $application['leave_type_id'],
                                  $application['days_requested'], $conn, 'use');
                
                $conn->commit();
                $_SESSION['flash_message'] = "Leave application approved by department head.";
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

        header("Location: leave_management.php?tab=manage");
        exit();
    }

    if ($action === 'dept_head_reject' && isset($_GET['id']) && hasPermission('dept_head')) {
        $leaveId = (int)$_GET['id'];

        try {
            $conn->begin_transaction();

            // Get application details
            $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
            $stmt->bind_param("i", $leaveId);
            $stmt->execute();
            $application = $stmt->get_result()->fetch_assoc();

            // Get current user's employee record
           $userEmpQuery = "SELECT id FROM employees WHERE employee_id = (SELECT employee_id FROM users WHERE id = ?)";
            $stmt = $conn->prepare($userEmpQuery);
            $stmt->bind_param("s", $user['id']);
            $stmt->execute();
            $userEmployee = $stmt->get_result()->fetch_assoc();

            // Get employee's department ID
            $empDeptQuery = "SELECT department_id FROM employees WHERE id = ?";
            $stmt = $conn->prepare($empDeptQuery);
            $stmt->bind_param("i", $application['employee_id']);
            $stmt->execute();
            $empDeptResult = $stmt->get_result();
            $empDept = $empDeptResult->fetch_assoc();
            
            // Allow department head to reject application if employee is in their department
            if ($userEmployee && $application && $application['status'] === 'pending_dept_head' &&
                $empDept['department_id'] == $userEmployee['department_id']) {
                // Update application status to rejected
                $stmt = $conn->prepare("UPDATE leave_applications SET status = 'rejected', dept_head_approval = 'rejected', dept_head_approver_id = ?, dept_head_approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $userEmployee['id'], $leaveId);
                $stmt->execute();

                $conn->commit();
                $_SESSION['flash_message'] = "Leave application rejected!";
                $_SESSION['flash_type'] = "warning";
            } else {
                $conn->rollback();
                $_SESSION['flash_message'] = "You are not authorized to reject this leave application.";
                $_SESSION['flash_type'] = "danger";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash_message'] = "Database error: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }

        header("Location: leave_management.php?tab=manage");
        exit();
    }
}

?>

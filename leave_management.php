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

// Enhanced helper functions for leave management with deduction logic
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

function getLeaveTypeDetails($leaveTypeId, $conn) {
    $query = "SELECT * FROM leave_types WHERE id = ? AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $leaveTypeId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
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

function getAnnualLeaveBalance($employeeId, $conn) {
    // Assuming annual leave has a specific identifier (adjust as needed)
    $query = "SELECT lb.* FROM leave_balances lb 
              JOIN leave_types lt ON lb.leave_type_id = lt.id 
              WHERE lb.employee_id = ? AND lt.name LIKE '%Annual%' 
              ORDER BY lb.allocated DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row;
    }
    
    return ['allocated' => 0, 'used' => 0, 'remaining' => 0, 'leave_type_id' => null];
}

function calculateLeaveDeduction($employeeId, $leaveTypeId, $requestedDays, $conn) {
    $leaveType = getLeaveTypeDetails($leaveTypeId, $conn);
    $leaveBalance = getLeaveTypeBalance($employeeId, $leaveTypeId, $conn);
    
    $deductionPlan = [
        'primary_deduction' => 0,
        'annual_deduction' => 0,
        'unpaid_days' => 0,
        'warnings' => [],
        'is_valid' => true,
        'total_days' => $requestedDays
    ];
    
    if (!$leaveType) {
        $deductionPlan['is_valid'] = false;
        $deductionPlan['warnings'][] = "Invalid leave type selected.";
        return $deductionPlan;
    }
    
    // Check if requested days exceed maximum allowed per year
    if ($leaveType['max_days_per_year'] && $requestedDays > $leaveType['max_days_per_year']) {
        $deductionPlan['warnings'][] = "Requested days ({$requestedDays}) exceed maximum allowed per year ({$leaveType['max_days_per_year']}).";
    }
    
    $availablePrimaryBalance = $leaveBalance['remaining'];
    
    if ($requestedDays <= $availablePrimaryBalance) {
        // Sufficient balance in primary leave type
        $deductionPlan['primary_deduction'] = $requestedDays;
        $deductionPlan['warnings'][] = "Will be deducted from {$leaveType['name']} balance.";
    } else {
        // Insufficient balance in primary leave type
        $primaryUsed = $availablePrimaryBalance;
        $remainingDays = $requestedDays - $primaryUsed;
        
        $deductionPlan['primary_deduction'] = $primaryUsed;
        
        // Check if fallback to annual leave is allowed
        if ($leaveType['deducted_from_annual'] == 1 && $remainingDays > 0) {
            $annualBalance = getAnnualLeaveBalance($employeeId, $conn);
            
            if ($annualBalance['remaining'] >= $remainingDays) {
                // Sufficient annual leave balance
                $deductionPlan['annual_deduction'] = $remainingDays;
                $deductionPlan['warnings'][] = "Primary balance insufficient. {$primaryUsed} days from {$leaveType['name']}, {$remainingDays} days from Annual Leave.";
            } else {
                // Insufficient annual leave balance
                $annualUsed = $annualBalance['remaining'];
                $unpaidDays = $remainingDays - $annualUsed;
                
                $deductionPlan['annual_deduction'] = $annualUsed;
                $deductionPlan['unpaid_days'] = $unpaidDays;
                $deductionPlan['warnings'][] = "Insufficient leave balance. {$primaryUsed} days from {$leaveType['name']}, {$annualUsed} days from Annual Leave, {$unpaidDays} days will be unpaid.";
            }
        } else {
            // No fallback allowed or available
            $deductionPlan['unpaid_days'] = $remainingDays;
            if ($primaryUsed > 0) {
                $deductionPlan['warnings'][] = "{$primaryUsed} days from {$leaveType['name']}, {$remainingDays} days will be unpaid.";
            } else {
                $deductionPlan['warnings'][] = "No available balance. All {$requestedDays} days will be unpaid.";
            }
        }
    }
    
    return $deductionPlan;
}

function processLeaveDeduction($employeeId, $leaveTypeId, $deductionPlan, $conn) {
    $conn->begin_transaction();
    
    try {
        // Deduct from primary leave type
        if ($deductionPlan['primary_deduction'] > 0) {
            updateLeaveBalance($employeeId, $leaveTypeId, $deductionPlan['primary_deduction'], $conn, 'use');
        }
        
        // Deduct from annual leave if applicable
        if ($deductionPlan['annual_deduction'] > 0) {
            $annualBalance = getAnnualLeaveBalance($employeeId, $conn);
            if ($annualBalance['leave_type_id']) {
                updateLeaveBalance($employeeId, $annualBalance['leave_type_id'], $deductionPlan['annual_deduction'], $conn, 'use');
            }
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function updateLeaveBalance($employeeId, $leaveTypeId, $days, $conn, $action = 'use') {
    $balance = getLeaveTypeBalance($employeeId, $leaveTypeId, $conn);

    if ($action == 'use') {
        $newUsed = $balance['used'] + $days;
        $newRemaining = max(0, $balance['allocated'] - $newUsed);
    } else {
        $newUsed = max(0, $balance['used'] - $days);
        $newRemaining = $balance['allocated'] - $newUsed;
    }

    // Check if balance record exists
    if ($balance['allocated'] > 0 || $balance['used'] > 0) {
        $query = "UPDATE leave_balances SET used = ?, remaining = ? WHERE employee_id = ? AND leave_type_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $newUsed, $newRemaining, $employeeId, $leaveTypeId);
    } else {
        // Create new balance record if it doesn't exist
        $query = "INSERT INTO leave_balances (employee_id, leave_type_id, allocated, used, remaining) VALUES (?, ?, 0, ?, ?)";
        $stmt = $conn->prepare($query);
        $newRemaining = -$newUsed; // Negative remaining indicates overdraft
        $stmt->bind_param("iiii", $employeeId, $leaveTypeId, $newUsed, $newRemaining);
    }
    
    return $stmt->execute();
}

function logLeaveTransaction($applicationId, $employeeId, $leaveTypeId, $days, $deductionPlan, $conn) {
    $transactionData = [
        'primary_leave_type' => $leaveTypeId,
        'primary_days' => $deductionPlan['primary_deduction'],
        'annual_days' => $deductionPlan['annual_deduction'],
        'unpaid_days' => $deductionPlan['unpaid_days'],
        'warnings' => implode('; ', $deductionPlan['warnings'])
    ];
    
    $query = "INSERT INTO leave_transactions 
              (application_id, employee_id, transaction_date, transaction_type, details) 
              VALUES (?, ?, NOW(), 'deduction', ?)";
    $stmt = $conn->prepare($query);
    $details = json_encode($transactionData);
    $stmt->bind_param("iis", $applicationId, $employeeId, $details);
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
            $employeeId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : ($userEmployee['id'] ?? 0);
            $leaveTypeId = (int)$_POST['leave_type_id'];
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            $reason = sanitizeInput($_POST['reason']);

            // Get leave type details for calculation
            $leaveType = getLeaveTypeDetails($leaveTypeId, $conn);
            
            if (!$leaveType) {
                $error = "Invalid leave type selected.";
                break;
            }

            // Calculate days based on leave type settings
            $days = calculateBusinessDays($startDate, $endDate, $conn, $leaveType['counts_weekends'] == 0);

            // Calculate deduction plan
            $deductionPlan = calculateLeaveDeduction($employeeId, $leaveTypeId, $days, $conn);
            
            if (!$deductionPlan['is_valid']) {
                $error = implode(' ', $deductionPlan['warnings']);
                break;
            }

            try {
                $conn->begin_transaction();

                // Get the section head and department head for this employee
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
                
                // Set initial status
                $initialStatus = 'pending_section_head';
                
                // Insert application with deduction details
                $deductionDetails = json_encode($deductionPlan);
                $stmt = $conn->prepare("INSERT INTO leave_applications
                    (employee_id, leave_type_id, start_date, end_date, days_requested, reason,
                     status, applied_at, section_head_emp_id, dept_head_emp_id, deduction_details,
                     primary_days, annual_days, unpaid_days)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisssssisiii", $employeeId, $leaveTypeId, $startDate, $endDate,
                                $days, $reason, $initialStatus, $sectionHeadEmpId, $deptHeadEmpId,
                                $deductionDetails,
                                $deductionPlan['primary_deduction'],
                                $deductionPlan['annual_deduction'],
                                $deductionPlan['unpaid_days']);

                if ($stmt->execute()) {
                    $applicationId = $conn->insert_id;
                    
                    // Log the transaction
                    logLeaveTransaction($applicationId, $employeeId, $leaveTypeId, $days, $deductionPlan, $conn);
                    
                    $conn->commit();
                    $warningMessages = implode('<br>', $deductionPlan['warnings']);
                    $success = "Leave application submitted successfully!<br><strong>Deduction Summary:</strong><br>" . $warningMessages;
                } else {
                    $conn->rollback();
                    $error = "Error submitting application.";
                }
            } catch (Exception $e) {
                $conn->rollback();
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

                    if ($application) {
                        // Process leave deductions
                        $deductionPlan = json_decode($application['deduction_details'], true);
                        processLeaveDeduction($application['employee_id'], $application['leave_type_id'], $deductionPlan, $conn);

                        // Update application status
                        $stmt = $conn->prepare("UPDATE leave_applications 
                                              SET status = 'approved', approver_id = ?, approver_comments = ?, 
                                                  approved_date = NOW() WHERE id = ?");
                        $stmt->bind_param("isi", $user['id'], $approverComments, $applicationId);
                        $stmt->execute();

                        $conn->commit();
                        $success = "Leave application approved successfully!";
                    } else {
                        $conn->rollback();
                        $error = "Application not found.";
                    }
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

// Handle GET actions for approvals (existing code continues...)
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Section Head Approval
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
        header("Location: leave_management.php?tab=manage");
        exit();
    }

    // Similar handling for other approval actions...
    // (dept_head_approve, dept_head_reject, section_head_reject)
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

    // Get leave types with enhanced details
    $leaveTypesResult = $conn->query("SELECT * FROM leave_types WHERE is_active = 1 ORDER BY name");
    $leaveTypes = $leaveTypesResult->fetch_all(MYSQLI_ASSOC);

    // Get employees (for managers)
    if (in_array($user['role'], ['hr_manager', 'dept_head', 'section_head'])) {
        $employeesQuery = "SELECT e.*, d.name as department_name, s.name as section_name 
                          FROM employees e 
                          LEFT JOIN departments d ON e.department_id = d.id 
                          LEFT JOIN sections s ON e.section_id = s.id";
        
        if ($user['role'] === 'dept_head') {
            $employeesQuery .= " WHERE e.department_id = " . (int)$userEmployee['department_id'];
        } elseif ($user['role'] === 'section_head') {
            $employeesQuery .= " WHERE e.section_id = " . (int)$userEmployee['section_id'];
        }
        
        $employeesQuery .= " ORDER BY e.first_name, e.last_name";
        $employees = $conn->query($employeesQuery)->fetch_all(MYSQLI_ASSOC);
    }

    // Get holidays
    $holidaysResult = $conn->query("SELECT * FROM holidays ORDER BY date DESC");
    $holidays = $holidaysResult->fetch_all(MYSQLI_ASSOC);

    // Get leave applications with enhanced deduction details
    if (hasPermission('hr_manager')) {
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

    // Get leave balances for current user with leave type details
    if ($userEmployee) {
        $stmt = $conn->prepare("SELECT lb.*, lt.name as leave_type_name, lt.max_days_per_year, lt.counts_weekends,
                               lt.deducted_from_annual
                               FROM leave_balances lb
                               JOIN leave_types lt ON lb.leave_type_id = lt.id
                               WHERE lb.employee_id = ? AND lt.is_active = 1
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
    <title>Enhanced Leave Management - HR Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .deduction-preview {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }
        .deduction-preview h5 {
            color: #495057;
            margin-bottom: 10px;
        }
        .deduction-item {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }
        .deduction-item:last-child {
            border-bottom: none;
            font-weight: bold;
        }
        .leave-balance-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .balance-header {
            font-weight: bold;
            color: #495057;
            margin-bottom: 8px;
        }
        .balance-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            text-align: center;
        }
        .balance-item {
            padding: 8px;
            border-radius: 5px;
            background: white;
        }
        .balance-allocated { border-left: 4px solid #007bff; }
        .balance-used { border-left: 4px solid #ffc107; }
        .balance-remaining { border-left: 4px solid #28a745; }
        .balance-negative { border-left: 4px solid #dc3545; }
        .warning-text {
            color: #856404;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 8px;
            border-radius: 4px;
            margin: 5px 0;
        }
        .info-text {
            color: #0c5460;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 8px;
            border-radius: 4px;
            margin: 5px 0;
        }
        .unpaid-warning {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 8px;
            border-radius: 4px;
            margin: 5px 0;
            font-weight: bold;
        }
    </style>
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
                <h1>Enhanced Leave Management System</h1>
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
                        <?php echo $success; ?>
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
                <!-- Enhanced Apply Leave Tab -->
                <div class="tab-content">
                    <h3>Apply for Leave</h3>

                    <?php if ($userEmployee): ?>
                    <!-- Leave Balance Overview -->
                    <div class="leave-balance-overview mb-4">
                        <h4>Your Leave Balance Overview</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                            <?php foreach ($leaveBalances as $balance): ?>
                            <div class="leave-balance-card">
                                <div class="balance-header"><?php echo htmlspecialchars($balance['leave_type_name']); ?></div>
                                <div class="balance-details">
                                    <div class="balance-item balance-allocated">
                                        <div>Allocated</div>
                                        <strong><?php echo $balance['allocated']; ?></strong>
                                    </div>
                                    <div class="balance-item balance-used">
                                        <div>Used</div>
                                        <strong><?php echo $balance['used']; ?></strong>
                                    </div>
                                    <div class="balance-item <?php echo $balance['remaining'] < 0 ? 'balance-negative' : 'balance-remaining'; ?>">
                                        <div>Remaining</div>
                                        <strong><?php echo $balance['remaining']; ?></strong>
                                    </div>
                                </div>
                                <?php if ($balance['max_days_per_year']): ?>
                                <div class="info-text" style="margin-top: 8px; font-size: 12px;">
                                    Max per year: <?php echo $balance['max_days_per_year']; ?> days
                                    <?php if ($balance['deducted_from_annual']): ?>
                                    | Fallback to Annual Leave
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <form method="POST" action="" id="leaveApplicationForm">
                        <input type="hidden" name="action" value="apply_leave">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="employee_id">Employee</label>
                                <select id="employee_id" name="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    <?php 
                                    if ($userEmployee) {
                                        if (!in_array($user['role'], ['hr_manager', 'dept_head', 'section_head'])) {
                                            echo '<option value="' . $userEmployee['id'] . '" selected>' . 
                                                 htmlspecialchars(
                                                     $userEmployee['employee_id'] . ' - ' . 
                                                     $userEmployee['first_name'] . ' ' . 
                                                     $userEmployee['last_name'] . ' (' . 
                                                     ($userEmployee['designation'] ?? '') . ')'
                                                 ) . '</option>';
                                        } elseif (isset($employees) && is_array($employees)) {
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
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="leave_type_id">Leave Type</label>
                                <select name="leave_type_id" id="leave_type_id" class="form-control" required>
                                    <option value="">Select Leave Type</option>
                                    <?php foreach ($leaveTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>" 
                                            data-max-days="<?php echo $type['max_days_per_year']; ?>"
                                            data-counts-weekends="<?php echo $type['counts_weekends']; ?>"
                                            data-fallback="<?php echo $type['deducted_from_annual']; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?>
                                        <?php if ($type['max_days_per_year']): ?>
                                        (Max: <?php echo $type['max_days_per_year']; ?> days/year)
                                        <?php endif; ?>
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

                        <!-- Enhanced Deduction Preview -->
                        <div id="deduction_preview" class="deduction-preview" style="display: none;">
                            <h5>Leave Deduction Preview</h5>
                            <div id="deduction_details"></div>
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason for Leave</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="submit_btn">Submit Application</button>
                            <button type="reset" class="btn btn-secondary">Reset Form</button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        Your user account is not linked to an employee record. Please contact HR to resolve this issue.
                    </div>
                    <?php endif; ?>

                    <!-- Enhanced My Leave Applications -->
                    <div class="table-container mt-4">
                        <h3>My Leave Applications</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Deduction Details</th>
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
                                            <?php if (isset($application['primary_days'], $application['annual_days'], $application['unpaid_days'])): ?>
                                            <small>
                                                <?php if ($application['primary_days'] > 0): ?>
                                                Primary: <?php echo $application['primary_days']; ?><br>
                                                <?php endif; ?>
                                                <?php if ($application['annual_days'] > 0): ?>
                                                Annual: <?php echo $application['annual_days']; ?><br>
                                                <?php endif; ?>
                                                <?php if ($application['unpaid_days'] > 0): ?>
                                                <span style="color: #dc3545;">Unpaid: <?php echo $application['unpaid_days']; ?></span>
                                                <?php endif; ?>
                                            </small>
                                            <?php else: ?>
                                            <small class="text-muted">Legacy application</small>
                                            <?php endif; ?>
                                        </td>
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
                                        <td colspan="8" class="text-center text-muted">No leave applications found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php elseif ($tab === 'profile'): ?>
                <!-- Enhanced My Leave Profile Tab -->
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
                        </div>
                    </div>

                    <!-- Enhanced Leave Balance Display -->
                    <div class="leave-balance-section mb-4">
                        <h4>Detailed Leave Balance (Current Year)</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
                            <?php foreach ($leaveBalances as $balance): ?>
                            <div class="leave-balance-card">
                                <div class="balance-header">
                                    <?php echo htmlspecialchars($balance['leave_type_name']); ?>
                                    <?php if ($balance['max_days_per_year']): ?>
                                    <span style="font-size: 12px; color: #6c757d;">(Max: <?php echo $balance['max_days_per_year']; ?>/year)</span>
                                    <?php endif; ?>
                                </div>
                                <div class="balance-details">
                                    <div class="balance-item balance-allocated">
                                        <div>Allocated</div>
                                        <strong><?php echo $balance['allocated']; ?> days</strong>
                                    </div>
                                    <div class="balance-item balance-used">
                                        <div>Used</div>
                                        <strong><?php echo $balance['used']; ?> days</strong>
                                    </div>
                                    <div class="balance-item <?php echo $balance['remaining'] < 0 ? 'balance-negative' : 'balance-remaining'; ?>">
                                        <div>Remaining</div>
                                        <strong><?php echo $balance['remaining']; ?> days</strong>
                                    </div>
                                </div>
                                <div style="margin-top: 10px; font-size: 12px;">
                                    <?php if ($balance['counts_weekends'] == 0): ?>
                                    <div class="info-text">Working days only (excludes weekends)</div>
                                    <?php else: ?>
                                    <div class="info-text">Includes weekends</div>
                                    <?php endif; ?>
                                    
                                    <?php if ($balance['deducted_from_annual']): ?>
                                    <div class="info-text">Falls back to Annual Leave when exhausted</div>
                                    <?php endif; ?>
                                    
                                    <?php if ($balance['remaining'] < 0): ?>
                                    <div class="unpaid-warning">Negative balance - previous leave was unpaid</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Enhanced Leave History -->
                    <div class="table-container">
                        <h4>My Leave History</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Deduction Breakdown</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($leaveHistory)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No leave applications found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($leaveHistory as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['leave_type_name']); ?></td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td>
                                            <?php if (isset($leave['primary_days'], $leave['annual_days'], $leave['unpaid_days'])): ?>
                                            <small>
                                                <?php if ($leave['primary_days'] > 0): ?>
                                                Primary: <?php echo $leave['primary_days']; ?><br>
                                                <?php endif; ?>
                                                <?php if ($leave['annual_days'] > 0): ?>
                                                Annual: <?php echo $leave['annual_days']; ?><br>
                                                <?php endif; ?>
                                                <?php if ($leave['unpaid_days'] > 0): ?>
                                                <span style="color: #dc3545;">Unpaid: <?php echo $leave['unpaid_days']; ?></span>
                                                <?php endif; ?>
                                            </small>
                                            <?php else: ?>
                                            <small class="text-muted">Not specified</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($leave['applied_at']); ?></td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadgeClass($leave['status']); ?>">
                                                <?php echo getStatusDisplayName($leave['status']); ?>
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
                <!-- Other tabs remain the same -->
                <div class="tab-content">
                    <p>Tab content under development or access denied.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Enhanced JavaScript for real-time leave deduction calculation
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const leaveTypeInput = document.getElementById('leave_type_id');
            const employeeInput = document.getElementById('employee_id');
            const calculatedDays = document.getElementById('calculated_days');
            const deductionPreview = document.getElementById('deduction_preview');
            const deductionDetails = document.getElementById('deduction_details');
            const submitBtn = document.getElementById('submit_btn');

            // Leave balances data (populated from PHP)
            const leaveBalances = <?php echo json_encode($leaveBalances); ?>;
            const leaveTypes = <?php echo json_encode($leaveTypes); ?>;

            function calculateDays() {
                if (startDateInput.value && endDateInput.value && leaveTypeInput.value) {
                    const start = new Date(startDateInput.value);
                    const end = new Date(endDateInput.value);
                    const leaveTypeId = parseInt(leaveTypeInput.value);
                    
                    if (end >= start) {
                        const selectedLeaveType = leaveTypes.find(lt => lt.id == leaveTypeId);
                        const countsWeekends = selectedLeaveType ? selectedLeaveType.counts_weekends == '1' : false;
                        
                        let diffDays = 0;
                        let current = new Date(start);
                        
                        while (current <= end) {
                            const dayOfWeek = current.getDay(); // 0 = Sunday, 6 = Saturday
                            
                            // Count weekends based on leave type setting
                            if (countsWeekends || (dayOfWeek !== 0 && dayOfWeek !== 6)) {
                                diffDays++;
                            }
                            
                            current.setDate(current.getDate() + 1);
                        }
                        
                        calculatedDays.value = diffDays + ' days';
                        calculateDeduction(leaveTypeId, diffDays);
                    } else {
                        calculatedDays.value = 'Invalid date range';
                        deductionPreview.style.display = 'none';
                    }
                } else {
                    calculatedDays.value = '';
                    deductionPreview.style.display = 'none';
                }
            }

            function calculateDeduction(leaveTypeId, requestedDays) {
                const selectedLeaveType = leaveTypes.find(lt => lt.id == leaveTypeId);
                const leaveBalance = leaveBalances.find(lb => lb.leave_type_id == leaveTypeId);
                const annualBalance = leaveBalances.find(lb => lb.leave_type_name.includes('Annual'));

                if (!selectedLeaveType || !leaveBalance) {
                    deductionPreview.style.display = 'none';
                    return;
                }

                let deductionHtml = '';
                let primaryDeduction = 0;
                let annualDeduction = 0;
                let unpaidDays = 0;
                let warnings = [];

                const availablePrimaryBalance = parseInt(leaveBalance.remaining);
                
                // Check maximum days per year
                if (selectedLeaveType.max_days_per_year && requestedDays > parseInt(selectedLeaveType.max_days_per_year)) {
                    warnings.push(`⚠️ Requested days (${requestedDays}) exceed maximum allowed per year (${selectedLeaveType.max_days_per_year}).`);
                }

                if (requestedDays <= availablePrimaryBalance) {
                    // Sufficient balance in primary leave type
                    primaryDeduction = requestedDays;
                    warnings.push(`✅ Will be deducted from ${selectedLeaveType.name} balance.`);
                } else {
                    // Insufficient balance in primary leave type
                    primaryDeduction = Math.max(0, availablePrimaryBalance);
                    let remainingDays = requestedDays - primaryDeduction;

                    // Check if fallback to annual leave is allowed
                    if (selectedLeaveType.deducted_from_annual == '1' && remainingDays > 0 && annualBalance) {
                        const availableAnnualBalance = parseInt(annualBalance.remaining);
                        
                        if (availableAnnualBalance >= remainingDays) {
                            // Sufficient annual leave balance
                            annualDeduction = remainingDays;
                            warnings.push(`⚠️ Primary balance insufficient. ${primaryDeduction} days from ${selectedLeaveType.name}, ${annualDeduction} days from Annual Leave.`);
                        } else {
                            // Insufficient annual leave balance
                            annualDeduction = Math.max(0, availableAnnualBalance);
                            unpaidDays = remainingDays - annualDeduction;
                            warnings.push(`❌ Insufficient leave balance. ${primaryDeduction} days from ${selectedLeaveType.name}, ${annualDeduction} days from Annual Leave, ${unpaidDays} days will be unpaid.`);
                        }
                    } else {
                        // No fallback allowed
                        unpaidDays = remainingDays;
                        if (primaryDeduction > 0) {
                            warnings.push(`❌ ${primaryDeduction} days from ${selectedLeaveType.name}, ${unpaidDays} days will be unpaid.`);
                        } else {
                            warnings.push(`❌ No available balance. All ${requestedDays} days will be unpaid.`);
                        }
                    }
                }

                // Build deduction HTML
                deductionHtml += '<div class="deduction-item"><span>Requested Days:</span><span>' + requestedDays + '</span></div>';
                
                if (primaryDeduction > 0) {
                    deductionHtml += '<div class="deduction-item"><span>' + selectedLeaveType.name + ' Deduction:</span><span>' + primaryDeduction + ' days</span></div>';
                }
                
                if (annualDeduction > 0) {
                    deductionHtml += '<div class="deduction-item"><span>Annual Leave Deduction:</span><span>' + annualDeduction + ' days</span></div>';
                }
                
                if (unpaidDays > 0) {
                    deductionHtml += '<div class="deduction-item" style="color: #dc3545;"><span>Unpaid Days:</span><span>' + unpaidDays + ' days</span></div>';
                }

                // Add warnings
                warnings.forEach(function(warning) {
                    let warningClass = 'info-text';
                    if (warning.includes('❌') || warning.includes('unpaid')) {
                        warningClass = 'unpaid-warning';
                    } else if (warning.includes('⚠️')) {
                        warningClass = 'warning-text';
                    }
                    deductionHtml += '<div class="' + warningClass + '">' + warning + '</div>';
                });

                deductionDetails.innerHTML = deductionHtml;
                deductionPreview.style.display = 'block';

                // Enable/disable submit button based on unpaid days
                if (unpaidDays > 0) {
                    submitBtn.innerHTML = 'Submit Application (Includes Unpaid Leave)';
                    submitBtn.className = 'btn btn-warning';
                } else {
                    submitBtn.innerHTML = 'Submit Application';
                    submitBtn.className = 'btn btn-primary';
                }
            }

            // Event listeners
            startDateInput.addEventListener('change', calculateDays);
            endDateInput.addEventListener('change', calculateDays);
            leaveTypeInput.addEventListener('change', calculateDays);

            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            if (startDateInput) {
                startDateInput.min = today;
            }
            if (endDateInput) {
                endDateInput.min = today;
            }

            // Update end date minimum when start date changes
            startDateInput.addEventListener('change', function() {
                if (endDateInput) {
                    endDateInput.min = startDateInput.value;
                }
            });
        });
    </script>

</body>
</html>

<?php
// Enhanced GET actions for approvals with deduction processing
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Section Head Reject
    if ($action === 'section_head_reject' && isset($_GET['id']) && hasPermission('section_head')) {
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
                
                $stmt = $conn->prepare("UPDATE leave_applications SET status = 'rejected', section_head_approval = 'rejected', section_head_approved_by = ?, section_head_approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $userEmpRecord['id'], $leaveId);
                $stmt->execute();
                
                // Log rejection transaction
                logLeaveTransaction($leaveId, $application['employee_id'], $application['leave_type_id'], 
                                  $application['days_requested'], 
                                  ['warnings' => ['Application rejected by section head']], $conn);
                
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

    // Department Head Approve
    if ($action === 'dept_head_approve' && isset($_GET['id']) && hasPermission('dept_head')) {
        $leaveId = (int)$_GET['id'];

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
            $stmt->bind_param("i", $leaveId);
            $stmt->execute();
            $application = $stmt->get_result()->fetch_assoc();

            $userEmpQuery = "SELECT id FROM employees WHERE employee_id = (SELECT employee_id FROM users WHERE id = ?)";
            $stmt = $conn->prepare($userEmpQuery);
            $stmt->bind_param("s", $user['id']);
            $stmt->execute();
            $userEmpRecord = $stmt->get_result()->fetch_assoc();

            $empDeptQuery = "SELECT department_id FROM employees WHERE id = ?";
            $stmt = $conn->prepare($empDeptQuery);
            $stmt->bind_param("i", $application['employee_id']);
            $stmt->execute();
            $empDeptResult = $stmt->get_result();
            $empDept = $empDeptResult->fetch_assoc();
            
            if ($userEmpRecord && $application && $application['status'] === 'pending_dept_head' &&
                $empDept['department_id'] == $userEmployee['department_id']) {
                
                // Process leave deductions based on stored deduction plan
                if ($application['deduction_details']) {
                    $deductionPlan = json_decode($application['deduction_details'], true);
                    processLeaveDeduction($application['employee_id'], $application['leave_type_id'], $deductionPlan, $conn);
                }
                
                $stmt = $conn->prepare("UPDATE leave_applications SET status = 'approved', dept_head_approval = 'approved', dept_head_approved_by = ?, dept_head_approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $userEmpRecord['id'], $leaveId);
                $stmt->execute();
                
                $conn->commit();
                $_SESSION['flash_message'] = "Leave application approved by department head. Leave balances updated.";
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

    // Department Head Reject
    if ($action === 'dept_head_reject' && isset($_GET['id']) && hasPermission('dept_head')) {
        $leaveId = (int)$_GET['id'];

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
            $stmt->bind_param("i", $leaveId);
            $stmt->execute();
            $application = $stmt->get_result()->fetch_assoc();

            $userEmpQuery = "SELECT id FROM employees WHERE employee_id = (SELECT employee_id FROM users WHERE id = ?)";
            $stmt = $conn->prepare($userEmpQuery);
            $stmt->bind_param("s", $user['id']);
            $stmt->execute();
            $userEmpRecord = $stmt->get_result()->fetch_assoc();

            $empDeptQuery = "SELECT department_id FROM employees WHERE id = ?";
            $stmt = $conn->prepare($empDeptQuery);
            $stmt->bind_param("i", $application['employee_id']);
            $stmt->execute();
            $empDeptResult = $stmt->get_result();
            $empDept = $empDeptResult->fetch_assoc();
            
            if ($userEmpRecord && $application && $application['status'] === 'pending_dept_head' &&
                $empDept['department_id'] == $userEmployee['department_id']) {
                
                $stmt = $conn->prepare("UPDATE leave_applications SET status = 'rejected', dept_head_approval = 'rejected', dept_head_approved_by = ?, dept_head_approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $userEmpRecord['id'], $leaveId);
                $stmt->execute();

                // Log rejection transaction
                logLeaveTransaction($leaveId, $application['employee_id'], $application['leave_type_id'], 
                                  $application['days_requested'], 
                                  ['warnings' => ['Application rejected by department head']], $conn);

                $conn->commit();
                $_SESSION['flash_message'] = "Leave application rejected by department head.";
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

    // HR Final Approval with full deduction processing
    if ($action === 'approve_leave' && isset($_GET['id']) && hasPermission('hr_manager')) {
        $leaveId = (int)$_GET['id'];
        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("SELECT * FROM leave_applications WHERE id = ?");
            $stmt->bind_param("i", $leaveId);
            $stmt->execute();
            $application = $stmt->get_result()->fetch_assoc();

            if ($application) {
                // Process leave deductions if not already processed
                if ($application['deduction_details'] && $application['status'] !== 'approved') {
                    $deductionPlan = json_decode($application['deduction_details'], true);
                    processLeaveDeduction($application['employee_id'], $application['leave_type_id'], $deductionPlan, $conn);
                }

                $stmt = $conn->prepare("UPDATE leave_applications 
                                      SET status = 'approved', approver_id = ?, 
                                          approved_date = NOW() WHERE id = ?");
                $stmt->bind_param("si", $user['id'], $leaveId);
                $stmt->execute();

                $conn->commit();
                $_SESSION['flash_message'] = "Leave application approved by HR. All deductions processed.";
                $_SESSION['flash_type'] = "success";
            } else {
                $conn->rollback();
                $_SESSION['flash_message'] = "Application not found.";
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

    // HR Rejection
    if ($action === 'reject_leave' && isset($_GET['id']) && hasPermission('hr_manager')) {
        $leaveId = (int)$_GET['id'];
        try {
            $stmt = $conn->prepare("UPDATE leave_applications 
                                  SET status = 'rejected', approver_id = ?, 
                                      approved_date = NOW() WHERE id = ?");
            $stmt->bind_param("si", $user['id'], $leaveId);

            if ($stmt->execute()) {
                // Get application details for logging
                $appStmt = $conn->prepare("SELECT employee_id, leave_type_id, days_requested FROM leave_applications WHERE id = ?");
                $appStmt->bind_param("i", $leaveId);
                $appStmt->execute();
                $appResult = $appStmt->get_result()->fetch_assoc();
                
                if ($appResult) {
                    logLeaveTransaction($leaveId, $appResult['employee_id'], $appResult['leave_type_id'], 
                                      $appResult['days_requested'], 
                                      ['warnings' => ['Application rejected by HR']], $conn);
                }

                $_SESSION['flash_message'] = "Leave application rejected by HR.";
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

// Populate management tabs with enhanced data
if ($tab === 'manage' && in_array($user['role'], ['hr_manager', 'dept_head', 'section_head', 'manager', 'managing_director'])) {
    // Role-specific filtering with enhanced deduction information
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
}

// Get profile data with enhanced balance information
if ($tab === 'profile') {
    if ($userEmployee) {
        $employee = $userEmployee;

        // Get comprehensive leave history with deduction details
        $historyQuery = "SELECT la.*, lt.name as leave_type_name,
                         la.primary_days, la.annual_days, la.unpaid_days
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
?>
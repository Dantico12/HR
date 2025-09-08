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

// Get current user from session
$user = [
    'first_name' => $_SESSION['user_name'] ? explode(' ', $_SESSION['user_name'])[0] : 'User',
    'last_name' => $_SESSION['user_name'] ? (explode(' ', $_SESSION['user_name'])[1] ?? '') : '',
    'role' => $_SESSION['user_role'] ?? 'guest',
    'id' => $_SESSION['user_id']
];

// Permission check function
function hasPermission($requiredRole) {
    $userRole = $_SESSION['user_role'] ?? 'guest';
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
function getDeductionStatusBadge($status) {
    $badges = [
        'active' => 'badge-success',
        'inactive' => 'badge-danger',
        'pending' => 'badge-warning'
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

// Function to get employee ID from user ID
function getEmployeeIdFromUserId($conn, $user_id) {
    $user_id = $conn->real_escape_string($user_id);
    
    // Get the employee_id from users table first
    $query = "SELECT employee_id FROM users WHERE id = '$user_id'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $employee_id_from_user = $user['employee_id'];
        
        // Now find the corresponding id in employees table
        $employeeQuery = "SELECT id FROM employees WHERE employee_id = '$employee_id_from_user'";
        $employeeResult = $conn->query($employeeQuery);
        
        if ($employeeResult && $employeeResult->num_rows > 0) {
            $employee = $employeeResult->fetch_assoc();
            return $employee['id']; // This is the correct ID for the foreign key
        }
    }
    
    // Fallback: Get first admin user's employee ID from employees table
    $adminQuery = "SELECT e.id FROM employees e 
                  JOIN users u ON e.employee_id = u.employee_id 
                  WHERE u.role IN ('super_admin', 'hr_manager') 
                  LIMIT 1";
    $adminResult = $conn->query($adminQuery);
    if ($adminResult && $adminResult->num_rows > 0) {
        $admin = $adminResult->fetch_assoc();
        return $admin['id'];
    }
    
    return 1; // Final fallback
}

// Database connection
$conn = getConnection();

// Get the employee ID for the current user
$current_employee_id = getEmployeeIdFromUserId($conn, $user['id']);

// Handle deduction type actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_deduction_type']) && hasPermission('hr_manager')) {
        $type_name = $conn->real_escape_string($_POST['type_name']);
        $description = $conn->real_escape_string($_POST['description']);
        $calculation_method = $conn->real_escape_string($_POST['calculation_method']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $insertQuery = "INSERT INTO deduction_types (type_name, description, calculation_method, is_active, created_at, updated_at) 
                        VALUES ('$type_name', '$description', '$calculation_method', $is_active, NOW(), NOW())";
        
        if ($conn->query($insertQuery)) {
            $_SESSION['flash_message'] = "Deduction type added successfully";
            $_SESSION['flash_type'] = "success";
            header("Location: deductions.php");
            exit();
        } else {
            $_SESSION['flash_message'] = "Error adding deduction type: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
    }
    
    if (isset($_POST['update_deduction_type']) && hasPermission('hr_manager')) {
        $deduction_type_id = $conn->real_escape_string($_POST['deduction_type_id']);
        $type_name = $conn->real_escape_string($_POST['type_name']);
        $description = $conn->real_escape_string($_POST['description']);
        $calculation_method = $conn->real_escape_string($_POST['calculation_method']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $updateQuery = "UPDATE deduction_types SET 
                        type_name = '$type_name',
                        description = '$description',
                        calculation_method = '$calculation_method',
                        is_active = $is_active,
                        updated_at = NOW()
                        WHERE deduction_type_id = '$deduction_type_id'";
        
        if ($conn->query($updateQuery)) {
            $_SESSION['flash_message'] = "Deduction type updated successfully";
            $_SESSION['flash_type'] = "success";
            header("Location: deductions.php");
            exit();
        } else {
            $_SESSION['flash_message'] = "Error updating deduction type: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
    }
    
    if (isset($_POST['add_deduction']) && hasPermission('hr_manager')) {
        $emp_id = $conn->real_escape_string($_POST['emp_id']);
        $deduction_type_id = $conn->real_escape_string($_POST['deduction_type_id']);
        $amount = $conn->real_escape_string($_POST['amount']);
        $effective_date = $conn->real_escape_string($_POST['effective_date']);
        $end_date = !empty($_POST['end_date']) ? "'" . $conn->real_escape_string($_POST['end_date']) . "'" : "NULL";
        $status = $conn->real_escape_string($_POST['status']);
        $created_by = $current_employee_id;
        
        $insertQuery = "INSERT INTO employee_deductions (emp_id, deduction_type_id, amount, effective_date, end_date, status, created_by, created_at, updated_at) 
                        VALUES ('$emp_id', '$deduction_type_id', '$amount', '$effective_date', $end_date, '$status', '$created_by', NOW(), NOW())";
        
        if ($conn->query($insertQuery)) {
            $_SESSION['flash_message'] = "Deduction added successfully";
            $_SESSION['flash_type'] = "success";
            header("Location: deductions.php");
            exit();
        } else {
            $_SESSION['flash_message'] = "Error adding deduction: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
    }
    
    if (isset($_POST['update_deduction']) && hasPermission('hr_manager')) {
        $deduction_id = $conn->real_escape_string($_POST['deduction_id']);
        $deduction_type_id = $conn->real_escape_string($_POST['deduction_type_id']);
        $amount = $conn->real_escape_string($_POST['amount']);
        $effective_date = $conn->real_escape_string($_POST['effective_date']);
        $end_date = !empty($_POST['end_date']) ? "'" . $conn->real_escape_string($_POST['end_date']) . "'" : "NULL";
        $status = $conn->real_escape_string($_POST['status']);
        
        $updateQuery = "UPDATE employee_deductions SET 
                        deduction_type_id = '$deduction_type_id',
                        amount = '$amount',
                        effective_date = '$effective_date',
                        end_date = $end_date,
                        status = '$status',
                        updated_at = NOW()
                        WHERE deduction_id = '$deduction_id'";
        
        if ($conn->query($updateQuery)) {
            $_SESSION['flash_message'] = "Deduction updated successfully";
            $_SESSION['flash_type'] = "success";
            header("Location: deductions.php");
            exit();
        } else {
            $_SESSION['flash_message'] = "Error updating deduction: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
    }
    
    if (isset($_POST['allocate_selected']) && hasPermission('hr_manager')) {
        $deduction_type_id = $conn->real_escape_string($_POST['deduction_type_id']);
        $amount = $conn->real_escape_string($_POST['amount']);
        $effective_date = $conn->real_escape_string($_POST['effective_date']);
        $end_date = !empty($_POST['end_date']) ? "'" . $conn->real_escape_string($_POST['end_date']) . "'" : "NULL";
        $status = $conn->real_escape_string($_POST['status']);
        $created_by = $current_employee_id;
        $emp_ids = $_POST['emp_ids'] ?? [];
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($emp_ids as $emp_id_val) {
            $emp_id = $conn->real_escape_string($emp_id_val);
            
            $checkQuery = "SELECT deduction_id FROM employee_deductions 
                          WHERE emp_id = '$emp_id' AND deduction_type_id = '$deduction_type_id' 
                          AND status = 'active'";
            $checkResult = $conn->query($checkQuery);
            
            if ($checkResult->num_rows == 0) {
                $insertQuery = "INSERT INTO employee_deductions (emp_id, deduction_type_id, amount, effective_date, end_date, status, created_by, created_at, updated_at) 
                                VALUES ('$emp_id', '$deduction_type_id', '$amount', '$effective_date', $end_date, '$status', '$created_by', NOW(), NOW())";
                
                if ($conn->query($insertQuery)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
        }
        
        $_SESSION['flash_message'] = "Allocation complete: $successCount deductions added, $errorCount errors";
        $_SESSION['flash_type'] = $errorCount > 0 ? "warning" : "success";
        header("Location: deductions.php");
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_deduction_type' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $deleteQuery = "DELETE FROM deduction_types WHERE deduction_type_id = '$id'";
    if ($conn->query($deleteQuery)) {
        $_SESSION['flash_message'] = "Deduction type deleted successfully";
        $_SESSION['flash_type'] = "success";
        header("Location: deductions.php");
        exit();
    } else {
        $_SESSION['flash_message'] = "Error deleting deduction type: " . $conn->error;
        $_SESSION['flash_type'] = "danger";
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $deleteQuery = "DELETE FROM employee_deductions WHERE deduction_id = '$id'";
    if ($conn->query($deleteQuery)) {
        $_SESSION['flash_message'] = "Deduction record deleted successfully";
        $_SESSION['flash_type'] = "success";
        header("Location: deductions.php");
        exit();
    } else {
        $_SESSION['flash_message'] = "Error deleting record: " . $conn->error;
        $_SESSION['flash_type'] = "danger";
    }
}

// Fetch all employees for dropdown and list
$employees = [];
$employeesQuery = "SELECT id, first_name, last_name, scale_id FROM employees ORDER BY first_name, last_name";
$employeesResult = $conn->query($employeesQuery);
if ($employeesResult && $employeesResult->num_rows > 0) {
    while ($employee = $employeesResult->fetch_assoc()) {
        $employees[] = $employee;
    }
}

// Fetch all deduction types
$deductionTypes = [];
$typesQuery = "SELECT deduction_type_id, type_name, calculation_method FROM deduction_types WHERE is_active = TRUE ORDER BY type_name";
$typesResult = $conn->query($typesQuery);
if ($typesResult && $typesResult->num_rows > 0) {
    while ($type = $typesResult->fetch_assoc()) {
        $deductionTypes[$type['deduction_type_id']] = $type['type_name'];
    }
}

// Fetch all deduction types for management
$allDeductionTypes = [];
$allTypesQuery = "SELECT deduction_type_id, type_name, description, calculation_method, is_active, created_at, updated_at 
                  FROM deduction_types ORDER BY type_name";
$allTypesResult = $conn->query($allTypesQuery);
if ($allTypesResult && $allTypesResult->num_rows > 0) {
    while ($type = $allTypesResult->fetch_assoc()) {
        $allDeductionTypes[] = $type;
    }
}

// Get record for editing if action is edit
$editRecord = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $editQuery = "SELECT ed.*, e.first_name, e.last_name, e.scale_id, dt.type_name 
                  FROM employee_deductions ed
                  JOIN employees e ON ed.emp_id = e.id
                  JOIN deduction_types dt ON ed.deduction_type_id = dt.deduction_type_id
                  WHERE ed.deduction_id = '$id'";
    $editResult = $conn->query($editQuery);
    if ($editResult && $editResult->num_rows > 0) {
        $editRecord = $editResult->fetch_assoc();
    } else {
        $_SESSION['flash_message'] = "Error fetching record: " . ($editResult ? "No record found" : $conn->error);
        $_SESSION['flash_type'] = "danger";
        header("Location: deductions.php");
        exit();
    }
}

// Get deduction type for editing
$editDeductionType = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit_deduction_type' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $editQuery = "SELECT * FROM deduction_types WHERE deduction_type_id = '$id'";
    $editResult = $conn->query($editQuery);
    if ($editResult && $editResult->num_rows > 0) {
        $editDeductionType = $editResult->fetch_assoc();
    } else {
        $_SESSION['flash_message'] = "Error fetching deduction type: " . ($editResult ? "No record found" : $conn->error);
        $_SESSION['flash_type'] = "danger";
        header("Location: deductions.php");
        exit();
    }
}

// Pagination and sorting for deductions
$rowsPerPage = isset($_GET['rows']) && in_array($_GET['rows'], [25, 50, 100, 250, 500]) ? (int)$_GET['rows'] : 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage;

$sortBy = isset($_GET['sort']) && in_array($_GET['sort'], ['deduction_id', 'emp_id', 'type_name', 'amount', 'status']) ? $_GET['sort'] : 'deduction_id';
$sortOrder = isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC' ? 'DESC' : 'ASC';
$filter = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$whereClause = '';
if ($filter) {
    $whereClause = "WHERE e.first_name LIKE '%$filter%' OR e.last_name LIKE '%$filter%' OR dt.type_name LIKE '%$filter%' OR e.scale_id LIKE '%$filter%'";
}

$countQuery = "SELECT COUNT(*) as count FROM employee_deductions ed
               JOIN employees e ON ed.emp_id = e.id
               JOIN deduction_types dt ON ed.deduction_type_id = dt.deduction_type_id
               $whereClause";
$countResult = $conn->query($countQuery);
$totalRecords = $countResult->fetch_assoc()['count'];
$totalPages = ceil($totalRecords / $rowsPerPage);

$query = "SELECT ed.deduction_id, ed.emp_id, ed.deduction_type_id, ed.amount, ed.effective_date, 
                 ed.end_date, ed.status, e.first_name, e.last_name, e.scale_id, dt.type_name 
          FROM employee_deductions ed
          JOIN employees e ON ed.emp_id = e.id
          JOIN deduction_types dt ON ed.deduction_type_id = dt.deduction_type_id
          $whereClause 
          ORDER BY $sortBy $sortOrder 
          LIMIT $offset, $rowsPerPage";
$result = $conn->query($query);

$deductionRecords = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $deductionRecords[] = $row;
    }
} else {
    $_SESSION['flash_message'] = "Error fetching records: " . $conn->error;
    $_SESSION['flash_type'] = "danger";
}

// Pagination and sorting for deduction types
$typesRowsPerPage = isset($_GET['types_rows']) && in_array($_GET['types_rows'], [25, 50, 100]) ? (int)$_GET['types_rows'] : 25;
$typesPage = isset($_GET['types_page']) ? max(1, (int)$_GET['types_page']) : 1;
$typesOffset = ($typesPage - 1) * $typesRowsPerPage;

$typesSortBy = isset($_GET['types_sort']) && in_array($_GET['types_sort'], ['deduction_type_id', 'type_name', 'is_active', 'created_at']) ? $_GET['types_sort'] : 'type_name';
$typesSortOrder = isset($_GET['types_order']) && strtoupper($_GET['types_order']) === 'DESC' ? 'DESC' : 'ASC';
$typesFilter = isset($_GET['types_search']) ? $conn->real_escape_string($_GET['types_search']) : '';

$typesWhereClause = '';
if ($typesFilter) {
    $typesWhereClause = "WHERE type_name LIKE '%$typesFilter%' OR description LIKE '%$typesFilter%'";
}

$typesCountQuery = "SELECT COUNT(*) as count FROM deduction_types $typesWhereClause";
$typesCountResult = $conn->query($typesCountQuery);
$typesTotalRecords = $typesCountResult->fetch_assoc()['count'];
$typesTotalPages = ceil($typesTotalRecords / $typesRowsPerPage);

$typesQuery = "SELECT * FROM deduction_types $typesWhereClause 
               ORDER BY $typesSortBy $typesSortOrder 
               LIMIT $typesOffset, $typesRowsPerPage";
$typesResult = $conn->query($typesQuery);

$allDeductionTypes = [];
if ($typesResult) {
    while ($row = $typesResult->fetch_assoc()) {
        $allDeductionTypes[] = $row;
    }
}

$conn->close();

$pageTitle = "Deductions Management";
require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deductions - HR Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bulk-assign-section, .deduction-types-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .bulk-assign-section h4, .deduction-types-section h4 {
            margin-top: 0;
            color: #ffffff;
        }

        /* Table Styles */
        .table-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: transparent;
        }

        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .table th {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        .table tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
        }

        .badge-primary {
            background: #2a5298;
        }

        .badge-warning {
            background: #ffc107;
            color: #1e3c72;
        }

        .badge-secondary {
            background: rgba(255, 255, 255, 0.2);
        }

        .badge-success {
            background: #28a745;
        }

        .badge-danger {
            background: #dc3545;
        }

        /* Table Controls */
        .table-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            flex-wrap: nowrap;
        }

        .table-controls label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
            font-size: 14px;
            white-space: nowrap;
        }

        .table-controls select, .table-controls input {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .table-controls select:hover, .table-controls input:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .table-controls input {
            min-width: 150px;
        }

        /* Tabs Styles */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .tabs a {
            padding: 10px 20px;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px 8px 0 0;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .tabs a:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .tabs a.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .pagination-links a {
            margin: 0 5px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
        }

        .modal-dialog {
            max-width: 500px;
            margin: 100px auto;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 20px;
            color: #ffffff;
        }

        .modal-header, .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
        }

        .modal-body {
            padding: 15px 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: #ffffff;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transition: all 0.3s ease;
        }

        .form-control:hover, .form-control:focus {
            background: rgba(255, 255, 255, 0.3);
            outline: none;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .table-controls {
                flex-wrap: wrap;
                gap: 10px;
            }

            .tabs {
                flex-wrap: wrap;
                gap: 5px;
            }
        }

        @media (max-width: 576px) {
            .table-controls {
                flex-direction: column;
                align-items: flex-start;
            }

            .tabs {
                flex-direction: column;
            }
        }
        
        .calculation-method {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .method-option {
            flex: 1;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .method-option:hover {
            border-color: #4e73df;
            background: rgba(78, 115, 223, 0.1);
        }
        
        .method-option.selected {
            border-color: #4e73df;
            background: rgba(78, 115, 223, 0.2);
        }
        
        .method-option h4 {
            margin-bottom: 8px;
            color: #ffffff;
        }
        
        .method-option p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
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
            <nav class="nav">
                <ul>
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
                    <li><a href="employee_appraisal.php">
                        <i class="fas fa-star"></i> Performance Appraisal
                    </a></li>
                    <li><a href="payroll_management.php">
                        <i class="fas fa-money-check"></i> Payroll
                    </a></li>
                </ul>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="content">
                <!-- Tabs Navigation -->
                <div class="tabs">
                    <a href="payroll_management.php">Payroll Management</a>
                    <a href="deductions.php" class="active">Deductions</a>
                    <a href="allowances.php">Allowances</a>
                    <a href="add_bank.php">Add Banks</a>
                    <a href="periods.php">Periods</a>
                    <a href="mp_profile.php">MP Profile</a>
                </div>
                <div class="tab-nav">
                    <a href="#deductions" class="tab-link active" onclick="showTab('deductions')">Deduction Allocation</a>
                    <a href="#types" class="tab-link" onclick="showTab('types')">Deduction Types</a>
                </div>

                <?php $flash = getFlashMessage(); if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Allocate to Selected Employees Section -->
                <?php if (hasPermission('hr_manager')): ?>
                <div class="bulk-assign-section">
                    <h4>Allocate Deductions to Selected Employees</h4>
                    <form method="POST" action="deductions.php">
                        <input type="hidden" name="allocate_selected" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="alloc_deduction_type_id">Deduction Type</label>
                                <select class="form-control" id="alloc_deduction_type_id" name="deduction_type_id" required>
                                    <option value="">Select Deduction Type</option>
                                    <?php foreach ($deductionTypes as $id => $name): ?>
                                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="alloc_amount">Amount</label>
                                <input type="number" step="0.01" class="form-control" id="alloc_amount" name="amount" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="alloc_effective_date">Effective Date</label>
                                <input type="date" class="form-control" id="alloc_effective_date" name="effective_date" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="alloc_end_date">End Date (Optional)</label>
                                <input type="date" class="form-control" id="alloc_end_date" name="end_date">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="alloc_status">Status</label>
                                <select class="form-control" id="alloc_status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select_all" onclick="toggleSelectAll()"> Select All</th>
                                        <th>Employee Name</th>
                                        <th>Scale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><input type="checkbox" name="emp_ids[]" value="<?php echo $employee['id']; ?>" class="emp-checkbox"></td>
                                        <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['scale_id'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Allocate Deduction</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Deduction Types Section -->
                <?php if (hasPermission('hr_manager')): ?>
                <div class="deduction-types-section">
                    <div class="d-flex justify-between align-center mb-3">
                        <h3>Deduction Types</h3>
                        <button class="btn btn-primary" onclick="document.getElementById('addTypeModal').style.display='block'">
                            <i class="fas fa-plus"></i> Add Deduction Type
                        </button>
                    </div>
                    
                    <div class="table-controls">
                        <form method="GET" action="deductions.php">
                            <label for="types_search">Search:</label>
                            <input type="text" id="types_search" name="types_search" value="<?php echo htmlspecialchars($typesFilter); ?>" placeholder="Search by type name or description">
                            <input type="hidden" name="types_page" value="<?php echo $typesPage; ?>">
                            <input type="hidden" name="types_rows" value="<?php echo $typesRowsPerPage; ?>">
                            <input type="hidden" name="types_sort" value="<?php echo $typesSortBy; ?>">
                            <input type="hidden" name="types_order" value="<?php echo $typesSortOrder; ?>">
                            <button type="submit" class="btn btn-sm btn-primary">Search</button>
                        </form>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type Name</th>
                                <th>Description</th>
                                <th>Calculation Method</th>
                                <th>Active</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allDeductionTypes)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No deduction types found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allDeductionTypes as $type): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($type['type_name']); ?></td>
                                    <td><?php echo htmlspecialchars($type['description']); ?></td>
                                    <td><?php echo htmlspecialchars($type['calculation_method']); ?></td>
                                    <td><?php echo $type['is_active'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo formatDate($type['created_at']); ?></td>
                                    <td><?php echo formatDate($type['updated_at']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-type-btn" 
                                                data-id="<?php echo $type['deduction_type_id']; ?>"
                                                data-type-name="<?php echo htmlspecialchars($type['type_name']); ?>"
                                                data-description="<?php echo htmlspecialchars($type['description']); ?>"
                                                data-calculation-method="<?php echo htmlspecialchars($type['calculation_method']); ?>"
                                                data-is-active="<?php echo $type['is_active']; ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-type-btn" 
                                                data-id="<?php echo $type['deduction_type_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($type['type_name']); ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination for Deduction Types -->
                    <div class="pagination">
                        <p>Showing <?php echo min($typesTotalRecords, $typesOffset + 1); ?> to <?php echo min($typesTotalRecords, $typesOffset + $typesRowsPerPage); ?> of <?php echo $typesTotalRecords; ?> entries</p>
                        <div class="pagination-links">
                            <?php if ($typesPage > 1): ?>
                                <a href="deductions.php?types_page=<?php echo $typesPage - 1; ?>&types_rows=<?php echo $typesRowsPerPage; ?>&types_sort=<?php echo $typesSortBy; ?>&types_order=<?php echo $typesSortOrder; ?>&types_search=<?php echo urlencode($typesFilter); ?>" class="btn btn-sm btn-secondary">Previous</a>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $typesTotalPages; $i++): ?>
                                <a href="deductions.php?types_page=<?php echo $i; ?>&types_rows=<?php echo $typesRowsPerPage; ?>&types_sort=<?php echo $typesSortBy; ?>&types_order=<?php echo $typesSortOrder; ?>&types_search=<?php echo urlencode($typesFilter); ?>" class="btn btn-sm <?php echo $i == $typesPage ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                            <?php if ($typesPage < $typesTotalPages): ?>
                                <a href="deductions.php?types_page=<?php echo $typesPage + 1; ?>&types_rows=<?php echo $typesRowsPerPage; ?>&types_sort=<?php echo $typesSortBy; ?>&types_order=<?php echo $typesSortOrder; ?>&types_search=<?php echo urlencode($typesFilter); ?>" class="btn btn-sm btn-secondary">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Deduction Modal -->
    <div id="addModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Deduction</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="deductions.php">
                    <div class="modal-body">
                        <input type="hidden" name="add_deduction" value="1">
                        
                        <div class="form-group">
                            <label class="form-label" for="add_emp_id">Employee</label>
                            <select class="form-control" id="add_emp_id" name="emp_id" required>
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee['id']; ?>"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'] . ($employee['scale_id'] ? ' (Scale ' . $employee['scale_id'] . ')' : '')); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="add_deduction_type_id">Deduction Type</label>
                            <select class="form-control" id="add_deduction_type_id" name="deduction_type_id" required>
                                <option value="">Select Deduction Type</option>
                                <?php foreach ($deductionTypes as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="add_amount">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="add_amount" name="amount" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="add_effective_date">Effective Date</label>
                                <input type="date" class="form-control" id="add_effective_date" name="effective_date" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="add_end_date">End Date (Optional)</label>
                                <input type="date" class="form-control" id="add_end_date" name="end_date">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="add_status">Status</label>
                            <select class="form-control" id="add_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Deduction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Deduction Modal -->
    <div id="editModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Deduction</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="deductions.php">
                    <div class="modal-body">
                        <input type="hidden" name="deduction_id" id="edit_deduction_id">
                        <input type="hidden" name="update_deduction" value="1">
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_emp_id">Employee</label>
                            <select class="form-control" id="edit_emp_id" name="emp_id" disabled>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee['id']; ?>"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'] . ($employee['scale_id'] ? ' (Scale ' . $employee['scale_id'] . ')' : '')); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Employee cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_deduction_type_id">Deduction Type</label>
                            <select class="form-control" id="edit_deduction_type_id" name="deduction_type_id" required>
                                <option value="">Select Deduction Type</option>
                                <?php foreach ($deductionTypes as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_amount">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="edit_amount" name="amount" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="edit_effective_date">Effective Date</label>
                                <input type="date" class="form-control" id="edit_effective_date" name="effective_date" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="edit_end_date">End Date (Optional)</label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_status">Status</label>
                            <select class="form-control" id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Deduction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Deduction Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the deduction record for <span id="delete_deduction_name"></span>?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="delete_confirm_btn" href="#" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Deduction Type Modal -->
    <div id="addTypeModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Deduction Type</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="deductions.php">
                    <div class="modal-body">
                        <input type="hidden" name="add_deduction_type" value="1">
                        
                        <div class="form-group">
                            <label class="form-label" for="add_type_name">Type Name</label>
                            <input type="text" class="form-control" id="add_type_name" name="type_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="add_description">Description</label>
                            <textarea class="form-control" id="add_description" name="description" rows="4"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Calculation Method</label>
                            <div class="calculation-method">
                                <div class="method-option" data-method="percentage" onclick="selectMethod('percentage')">
                                    <h4>Percentage</h4>
                                    <p>Calculate as percentage of salary</p>
                                </div>
                                <div class="method-option" data-method="fixed" onclick="selectMethod('fixed')">
                                    <h4>Fixed Amount</h4>
                                    <p>Fixed amount regardless of salary</p>
                                </div>
                                <div class="method-option" data-method="formula" onclick="selectMethod('formula')">
                                    <h4>Formula</h4>
                                    <p>Complex calculation formula</p>
                                </div>
                            </div>
                            <input type="hidden" id="calculation_method" name="calculation_method" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="add_is_active">Active</label>
                            <input type="checkbox" id="add_is_active" name="is_active" checked>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Deduction Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Deduction Type Modal -->
    <div id="editTypeModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Deduction Type</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="deductions.php">
                    <div class="modal-body">
                        <input type="hidden" name="deduction_type_id" id="edit_type_id">
                        <input type="hidden" name="update_deduction_type" value="1">
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_type_name">Type Name</label>
                            <input type="text" class="form-control" id="edit_type_name" name="type_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Calculation Method</label>
                            <div class="calculation-method">
                                <div class="method-option" data-method="percentage" onclick="selectEditMethod('percentage')">
                                    <h4>Percentage</h4>
                                    <p>Calculate as percentage of salary</p>
                                </div>
                                <div class="method-option" data-method="fixed" onclick="selectEditMethod('fixed')">
                                    <h4>Fixed Amount</h4>
                                    <p>Fixed amount regardless of salary</p>
                                </div>
                                <div class="method-option" data-method="formula" onclick="selectEditMethod('formula')">
                                    <h4>Formula</h4>
                                    <p>Complex calculation formula</p>
                                </div>
                            </div>
                            <input type="hidden" id="edit_calculation_method" name="calculation_method" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_is_active">Active</label>
                            <input type="checkbox" id="edit_is_active" name="is_active">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Deduction Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Deduction Type Modal -->
    <div id="deleteTypeModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the deduction type <span id="delete_type_name"></span>?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="delete_type_confirm_btn" href="#" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Show the selected tab content
            document.getElementById(tabName).style.display = 'block';
            
            // Update active tab link
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('active');
            });
            document.querySelector(`.tab-link[href="#${tabName}"]`).classList.add('active');
        }
        
        // Handle edit button clicks for deductions
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const deductionId = this.getAttribute('data-id');
                const empId = this.getAttribute('data-emp-id');
                const typeId = this.getAttribute('data-type-id');
                const amount = this.getAttribute('data-amount');
                const effectiveDate = this.getAttribute('data-effective-date');
                const endDate = this.getAttribute('data-end-date');
                const status = this.getAttribute('data-status');
                
                document.getElementById('edit_deduction_id').value = deductionId;
                document.getElementById('edit_emp_id').value = empId;
                document.getElementById('edit_deduction_type_id').value = typeId;
                document.getElementById('edit_amount').value = amount;
                document.getElementById('edit_effective_date').value = effectiveDate;
                document.getElementById('edit_end_date').value = endDate;
                document.getElementById('edit_status').value = status;
                
                document.getElementById('editModal').style.display = 'block';
            });
        });
        
        // Handle delete button clicks for deductions
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const deductionId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('delete_deduction_name').textContent = name;
                document.getElementById('delete_confirm_btn').href = `deductions.php?action=delete&id=${deductionId}`;
                
                document.getElementById('deleteModal').style.display = 'block';
            });
        });
        
        // Handle edit button clicks for deduction types
        document.querySelectorAll('.edit-type-btn').forEach(button => {
            button.addEventListener('click', function() {
                const typeId = this.getAttribute('data-id');
                const typeName = this.getAttribute('data-type-name');
                const description = this.getAttribute('data-description');
                const calculationMethod = this.getAttribute('data-calculation-method');
                const isActive = this.getAttribute('data-is-active') === '1';
                
                document.getElementById('edit_type_id').value = typeId;
                document.getElementById('edit_type_name').value = typeName;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_calculation_method').value = calculationMethod;
                document.getElementById('edit_is_active').checked = isActive;
                
                // Select the correct method option
                selectEditMethod(calculationMethod);
                
                document.getElementById('editTypeModal').style.display = 'block';
            });
        });
        
        // Handle delete button clicks for deduction types
        document.querySelectorAll('.delete-type-btn').forEach(button => {
            button.addEventListener('click', function() {
                const typeId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('delete_type_name').textContent = name;
                document.getElementById('delete_type_confirm_btn').href = `deductions.php?action=delete_deduction_type&id=${typeId}`;
                
                document.getElementById('deleteTypeModal').style.display = 'block';
            });
        });
        
        // Close modals when clicking on X
        document.querySelectorAll('.close').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });
        
        // Open add modal for deductions
        document.querySelector('[onclick*="addModal"]').addEventListener('click', function() {
            document.getElementById('addModal').style.display = 'block';
        });
        
        // Open add modal for deduction types
        document.querySelector('[onclick*="addTypeModal"]').addEventListener('click', function() {
            document.getElementById('addTypeModal').style.display = 'block';
        });
        
        // Select calculation method
        function selectMethod(method) {
            document.querySelectorAll('.method-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`.method-option[data-method="${method}"]`).classList.add('selected');
            document.getElementById('calculation_method').value = method;
        }
        
        // Select edit calculation method
        function selectEditMethod(method) {
            document.querySelectorAll('#editTypeModal .method-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`#editTypeModal .method-option[data-method="${method}"]`).classList.add('selected');
            document.getElementById('edit_calculation_method').value = method;
        }
        
        // Toggle select all checkboxes
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('select_all');
            const checkboxes = document.querySelectorAll('.emp-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }
    </script>
</body>
</html>
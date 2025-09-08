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
    'first_name' => isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'User',
    'last_name' => isset($_SESSION['user_name']) ? (explode(' ', $_SESSION['user_name'])[1] ?? '') : '',
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
function getEmployeeTypeBadge($type) {
    $badges = [
        'permanent' => 'badge-primary',
        'contract' => 'badge-warning',
        'temporary' => 'badge-secondary'
    ];
    return $badges[$type] ?? 'badge-light';
}

function getPayrollStatusBadge($status) {
    $badges = [
        'active' => 'badge-success',
        'inactive' => 'badge-danger'
    ];
    return $badges[$status] ?? 'badge-light';
}

function formatDate($date) {
    if (!$date) return 'N/A';
    return date('M d, Y', strtotime($date));
}

function formatCurrency($amount) {
    if ($amount === null || $amount === '') return 'N/A';
    return number_format((float)$amount, 2);
}

function formatNullable($value) {
    return $value === null || $value === '' ? 'N/A' : htmlspecialchars($value);
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

// Database connection
$conn = getConnection();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $deleteQuery = "DELETE FROM payroll WHERE payroll_id = '$id'";
    if ($conn->query($deleteQuery)) {
        $_SESSION['flash_message'] = "Payroll record deleted successfully";
        $_SESSION['flash_type'] = "success";
        header("Location: payroll_management.php");
        exit();
    } else {
        $_SESSION['flash_message'] = "Error deleting record: " . $conn->error;
        $_SESSION['flash_type'] = "danger";
    }
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_payroll']) && hasPermission('hr_manager')) {
    $payroll_id = $conn->real_escape_string($_POST['payroll_id']);
    $employment_type = $conn->real_escape_string($_POST['employment_type']);
    $status = $conn->real_escape_string($_POST['status']);
    $salary = $conn->real_escape_string($_POST['salary']);
    $bank_id = $conn->real_escape_string($_POST['bank_id']);
    $bank_account = $conn->real_escape_string($_POST['bank_account']);
    $job_group = $conn->real_escape_string($_POST['job_group']);
    $sha_number = $conn->real_escape_string($_POST['sha_number'] ?? '');
    $kra_pin = $conn->real_escape_string($_POST['kra_pin'] ?? '');
    $nssf = $conn->real_escape_string($_POST['nssf'] ?? '');
    $gross_pay = $conn->real_escape_string($_POST['gross_pay'] ?? '');
    $net_pay = $conn->real_escape_string($_POST['net_pay'] ?? '');
    
    $updateQuery = "UPDATE payroll SET 
                    employment_type = '$employment_type',
                    status = '$status',
                    salary = '$salary',
                    bank_id = '$bank_id',
                    bank_account = '$bank_account',
                    job_group = '$job_group',
                    SHA_number = NULLIF('$sha_number', ''),
                    KRA_pin = NULLIF('$kra_pin', ''),
                    NSSF = NULLIF('$nssf', ''),
                    Gross_pay = NULLIF('$gross_pay', ''),
                    net_pay = NULLIF('$net_pay', '')
                    WHERE payroll_id = '$payroll_id'";
    
    if ($conn->query($updateQuery)) {
        $_SESSION['flash_message'] = "Payroll record updated successfully";
        $_SESSION['flash_type'] = "success";
        header("Location: payroll_management.php");
        exit();
    } else {
        $_SESSION['flash_message'] = "Error updating record: " . $conn->error;
        $_SESSION['flash_type'] = "danger";
    }
}

// Fetch all banks for the dropdown
$banks = [];
$banksQuery = "SELECT bank_id, bank_name FROM banks ORDER BY bank_name";
$banksResult = $conn->query($banksQuery);
if ($banksResult && $banksResult->num_rows > 0) {
    while ($bank = $banksResult->fetch_assoc()) {
        $banks[$bank['bank_id']] = $bank['bank_name'];
    }
}

// Get record for editing if action is edit
$editRecord = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $editQuery = "SELECT p.payroll_id, p.emp_id, p.employment_type, p.status, p.salary, p.bank_id, p.bank_account, p.job_group, 
                         p.SHA_number, p.KRA_pin, p.NSSF, p.Gross_pay, p.net_pay, 
                         e.first_name, e.last_name, b.bank_name 
                  FROM payroll p 
                  LEFT JOIN employees e ON p.emp_id = e.id 
                  LEFT JOIN banks b ON p.bank_id = b.bank_id
                  WHERE p.payroll_id = '$id'";
    $editResult = $conn->query($editQuery);
    if ($editResult && $editResult->num_rows > 0) {
        $editRecord = $editResult->fetch_assoc();
    } else {
        $_SESSION['flash_message'] = "Error fetching record: " . ($editResult ? "No record found" : $conn->error);
        $_SESSION['flash_type'] = "danger";
        header("Location: payroll_management.php");
        exit();
    }
}

// Pagination and sorting
$rowsPerPage = isset($_GET['rows']) && in_array($_GET['rows'], [25, 50, 100, 250, 500]) ? (int)$_GET['rows'] : 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage;

$sortBy = isset($_GET['sort']) && in_array($_GET['sort'], ['payroll_id', 'emp_id', 'employment_type', 'status', 'SHA_number', 'KRA_pin', 'NSSF', 'Gross_pay', 'net_pay']) ? $_GET['sort'] : 'payroll_id';
$sortOrder = isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC' ? 'DESC' : 'ASC';
$filter = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$whereClause = '';
if ($filter) {
    $whereClause = "WHERE e.first_name LIKE '%$filter%' OR e.last_name LIKE '%$filter%'";
}

// Count total records for pagination
$countQuery = "SELECT COUNT(*) as count FROM payroll p 
               LEFT JOIN employees e ON p.emp_id = e.id 
               $whereClause";
$countResult = $conn->query($countQuery);
$totalRecords = $countResult->fetch_assoc()['count'];
$totalPages = ceil($totalRecords / $rowsPerPage);

// Fetch payroll data with employee names and bank names
$query = "SELECT p.payroll_id, p.emp_id, p.employment_type, p.status, p.salary, p.bank_id, 
                 p.bank_account, p.job_group, p.SHA_number, p.KRA_pin, p.NSSF, p.Gross_pay, p.net_pay, 
                 e.first_name, e.last_name, b.bank_name 
          FROM payroll p 
          LEFT JOIN employees e ON p.emp_id = e.id 
          LEFT JOIN banks b ON p.bank_id = b.bank_id
          $whereClause 
          ORDER BY $sortBy $sortOrder 
          LIMIT $offset, $rowsPerPage";
$result = $conn->query($query);

$payrollRecords = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $payrollRecords[] = $row;
    }
} else {
    $_SESSION['flash_message'] = "Error fetching records: " . $conn->error;
    $_SESSION['flash_type'] = "danger";
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll - HR Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 220px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

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
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                padding: 10px;
            }

            .table-controls {
                flex-direction: column;
                align-items: flex-start;
            }

            .tabs {
                flex-direction: column;
            }
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
                    <li><a href="dashboard.php">Dashboard</a></li>
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
                    <?php if (hasPermission('hr_manager') || hasPermission('super_admin') || hasPermission('dept_head') || hasPermission('officer')): ?>
                    <li><a href="leave_management.php">Leave Management</a></li>
                    <?php endif; ?>
                    <li><a href="employee_appraisal.php">Performance Appraisal</a></li>
                    <li><a href="payroll_management.php" class="active">Payroll</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Payroll Management</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    <span class="badge badge-info"><?php echo ucwords(str_replace('_', ' ', $user['role'])); ?></span>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>

            <div class="content">
                <!-- Tabs Navigation -->
                <div class="tabs">
                    <a href="payroll_management.php" class="active">Payroll Management</a>
                    <a href="deductions.php">Deductions</a>
                    <a href="allowances.php">Allowances</a>
                    <a href="add_bank.php">Add Banks</a>
                    <a href="periods.php">Periods</a>
                    <a href="mp_profile.php">MP Profile</a>
                </div>

                <?php $flash = getFlashMessage(); if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <h3>Payroll Records</h3>
                    <div class="table-controls">
                        <form method="GET" action="payroll_management.php">
                            <label for="search">Search by Employee Name:</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($filter); ?>" placeholder="Enter employee name">
                            <input type="hidden" name="page" value="<?php echo $page; ?>">
                            <input type="hidden" name="rows" value="<?php echo $rowsPerPage; ?>">
                            <input type="hidden" name="sort" value="<?php echo $sortBy; ?>">
                            <input type="hidden" name="order" value="<?php echo $sortOrder; ?>">
                            <button type="submit" class="btn btn-sm btn-primary">Search</button>
                        </form>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Employment Type</th>
                                <th>Status</th>
                                <th>Job Group</th>
                                <th>Salary</th>
                                <th>Bank Name</th>
                                <th>Bank Account</th>
                                <th>SHA Number</th>
                                <th>KRA Pin</th>
                                <th>NSSF</th>
                                <th>Gross Pay</th>
                                <th>Net Pay</th>
                                <?php if (hasPermission('hr_manager')): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payrollRecords)): ?>
                                <tr>
                                    <td colspan="<?php echo hasPermission('hr_manager') ? 13 : 12; ?>" class="text-center">No payroll records found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payrollRecords as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getEmployeeTypeBadge($record['employment_type'] ?? ''); ?>">
                                            <?php echo $record['employment_type'] ? ucwords($record['employment_type']) : 'N/A'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getPayrollStatusBadge($record['status'] ?? ''); ?>">
                                            <?php echo $record['status'] ? ucwords($record['status']) : 'N/A'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['job_group'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatCurrency($record['salary']); ?></td>
                                    <td><?php echo htmlspecialchars($record['bank_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatNullable($record['bank_account']); ?></td>
                                    <td><?php echo formatNullable($record['SHA_number']); ?></td>
                                    <td><?php echo formatNullable($record['KRA_pin']); ?></td>
                                    <td><?php echo formatNullable($record['NSSF']); ?></td>
                                    <td><?php echo formatCurrency($record['Gross_pay']); ?></td>
                                    <td><?php echo formatCurrency($record['net_pay']); ?></td>
                                    <?php if (hasPermission('hr_manager')): ?>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-btn" 
                                                data-id="<?php echo $record['payroll_id']; ?>" 
                                                data-employment-type="<?php echo htmlspecialchars($record['employment_type'] ?? ''); ?>"
                                                data-status="<?php echo htmlspecialchars($record['status'] ?? ''); ?>"
                                                data-salary="<?php echo htmlspecialchars($record['salary'] ?? ''); ?>"
                                                data-bank-id="<?php echo htmlspecialchars($record['bank_id'] ?? ''); ?>"
                                                data-bank-account="<?php echo htmlspecialchars($record['bank_account'] ?? ''); ?>"
                                                data-job-group="<?php echo htmlspecialchars($record['job_group'] ?? ''); ?>"
                                                data-sha-number="<?php echo htmlspecialchars($record['SHA_number'] ?? ''); ?>"
                                                data-kra-pin="<?php echo htmlspecialchars($record['KRA_pin'] ?? ''); ?>"
                                                data-nssf="<?php echo htmlspecialchars($record['NSSF'] ?? ''); ?>"
                                                data-gross-pay="<?php echo htmlspecialchars($record['Gross_pay'] ?? ''); ?>"
                                                data-net-pay="<?php echo htmlspecialchars($record['net_pay'] ?? ''); ?>"
                                                data-name="<?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn" 
                                                data-id="<?php echo $record['payroll_id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>">
                                            Delete
                                        </button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="pagination">
                        <p>Showing <?php echo min($totalRecords, $offset + 1); ?> to <?php echo min($totalRecords, $offset + $rowsPerPage); ?> of <?php echo $totalRecords; ?> entries</p>
                        <div class="pagination-links">
                            <?php if ($page > 1): ?>
                                <a href="payroll_management.php?page=<?php echo $page - 1; ?>&rows=<?php echo $rowsPerPage; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>&search=<?php echo urlencode($filter); ?>" class="btn btn-sm btn-secondary">Previous</a>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="payroll_management.php?page=<?php echo $i; ?>&rows=<?php echo $rowsPerPage; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>&search=<?php echo urlencode($filter); ?>" class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="payroll_management.php?page=<?php echo $page + 1; ?>&rows=<?php echo $rowsPerPage; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>&search=<?php echo urlencode($filter); ?>" class="btn btn-sm btn-secondary">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Payroll Record</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="payroll_management.php">
                    <div class="modal-body">
                        <input type="hidden" name="payroll_id" id="edit_payroll_id">
                        <input type="hidden" name="update_payroll" value="1">
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_name">Employee Name</label>
                            <input type="text" class="form-control" id="edit_name" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_employment_type">Employment Type</label>
                            <select class="form-control" id="edit_employment_type" name="employment_type" required>
                                <option value="permanent">Permanent</option>
                                <option value="contract">Contract</option>
                                <option value="temporary">Temporary</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_status">Status</label>
                            <select class="form-control" id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_salary">Salary</label>
                            <input type="number" step="0.01" class="form-control" id="edit_salary" name="salary" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_bank_id">Bank Name</label>
                            <select class="form-control" id="edit_bank_id" name="bank_id" required>
                                <option value="">Select Bank</option>
                                <?php foreach ($banks as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_bank_account">Bank Account</label>
                            <input type="text" class="form-control" id="edit_bank_account" name="bank_account" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_job_group">Job Group</label>
                            <input type="text" class="form-control" id="edit_job_group" name="job_group" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_sha_number">SHA Number</label>
                            <input type="text" class="form-control" id="edit_sha_number" name="sha_number">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_kra_pin">KRA Pin</label>
                            <input type="text" class="form-control" id="edit_kra_pin" name="kra_pin">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_nssf">NSSF</label>
                            <input type="text" class="form-control" id="edit_nssf" name="nssf">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_gross_pay">Gross Pay</label>
                            <input type="number" step="0.01" class="form-control" id="edit_gross_pay" name="gross_pay">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_net_pay">Net Pay</label>
                            <input type="number" step="0.01" class="form-control" id="edit_net_pay" name="net_pay">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the payroll record for <span id="delete_employee_name"></span>?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="delete_confirm_btn" href="#" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const payrollId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const employmentType = this.getAttribute('data-employment-type');
                const status = this.getAttribute('data-status');
                const salary = this.getAttribute('data-salary');
                const bankId = this.getAttribute('data-bank-id');
                const bankAccount = this.getAttribute('data-bank-account');
                const jobGroup = this.getAttribute('data-job-group');
                const shaNumber = this.getAttribute('data-sha-number');
                const kraPin = this.getAttribute('data-kra-pin');
                const nssf = this.getAttribute('data-nssf');
                const grossPay = this.getAttribute('data-gross-pay');
                const netPay = this.getAttribute('data-net-pay');
                
                document.getElementById('edit_payroll_id').value = payrollId;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_employment_type').value = employmentType;
                document.getElementById('edit_status').value = status;
                document.getElementById('edit_salary').value = salary;
                document.getElementById('edit_bank_id').value = bankId;
                document.getElementById('edit_bank_account').value = bankAccount;
                document.getElementById('edit_job_group').value = jobGroup;
                document.getElementById('edit_sha_number').value = shaNumber;
                document.getElementById('edit_kra_pin').value = kraPin;
                document.getElementById('edit_nssf').value = nssf;
                document.getElementById('edit_gross_pay').value = grossPay;
                document.getElementById('edit_net_pay').value = netPay;
                
                document.getElementById('editModal').style.display = 'block';
            });
        });
        
        // Handle delete button clicks
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const payrollId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('delete_employee_name').textContent = name;
                document.getElementById('delete_confirm_btn').href = `payroll_management.php?action=delete&id=${payrollId}`;
                
                document.getElementById('deleteModal').style.display = 'block';
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
    </script>
</body>
</html>
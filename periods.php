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
require_once 'header.php';

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
function getStatusBadge($status) {
    $badges = [
        'Draft' => 'badge-secondary',
        'Approved' => 'badge-primary',
        'Processed' => 'badge-warning',
        'Paid' => 'badge-success'
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

// Database connection
$conn = getConnection();

// Handle create period form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_period']) && hasPermission('hr_manager')) {
    $period_name = $conn->real_escape_string($_POST['period_name']);
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = $conn->real_escape_string($_POST['end_date']);
    $pay_date = $conn->real_escape_string($_POST['pay_date']);
    $frequency = $conn->real_escape_string($_POST['frequency']);
    $status = $conn->real_escape_string($_POST['status']);
    $is_locked = isset($_POST['is_locked']) ? 1 : 0;

    $insertQuery = "INSERT INTO payroll_periods (period_name, start_date, end_date, pay_date, frequency, status, is_locked) 
                    VALUES ('$period_name', '$start_date', '$end_date', '$pay_date', '$frequency', '$status', $is_locked)";
    
    if ($conn->query($insertQuery)) {
        $_SESSION['flash_message'] = "Payroll period created successfully";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_message'] = "Error creating period: " . $conn->error;
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: periods.php");
    exit();
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $checkQuery = "SELECT is_locked FROM payroll_periods WHERE id = '$id'";
    $checkResult = $conn->query($checkQuery);
    if ($checkResult && $checkResult->num_rows > 0) {
        $period = $checkResult->fetch_assoc();
        if ($period['is_locked']) {
            $_SESSION['flash_message'] = "Cannot delete a locked payroll period";
            $_SESSION['flash_type'] = "danger";
        } else {
            $deleteQuery = "DELETE FROM payroll_periods WHERE id = '$id'";
            if ($conn->query($deleteQuery)) {
                $_SESSION['flash_message'] = "Payroll period deleted successfully";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Error deleting period: " . $conn->error;
                $_SESSION['flash_type'] = "danger";
            }
        }
    } else {
        $_SESSION['flash_message'] = "Period not found";
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: periods.php");
    exit();
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_period']) && hasPermission('hr_manager')) {
    $period_id = $conn->real_escape_string($_POST['period_id']);
    $period_name = $conn->real_escape_string($_POST['period_name']);
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = $conn->real_escape_string($_POST['end_date']);
    $pay_date = $conn->real_escape_string($_POST['pay_date']);
    $frequency = $conn->real_escape_string($_POST['frequency']);
    $status = $conn->real_escape_string($_POST['status']);
    $is_locked = isset($_POST['is_locked']) ? 1 : 0;

    // Check if period is locked
    $checkQuery = "SELECT is_locked FROM payroll_periods WHERE id = '$period_id'";
    $checkResult = $conn->query($checkQuery);
    if ($checkResult && $checkResult->num_rows > 0 && $checkResult->fetch_assoc()['is_locked']) {
        $_SESSION['flash_message'] = "Cannot edit a locked payroll period";
        $_SESSION['flash_type'] = "danger";
    } else {
        $updateQuery = "UPDATE payroll_periods SET 
                        period_name = '$period_name',
                        start_date = '$start_date',
                        end_date = '$end_date',
                        pay_date = '$pay_date',
                        frequency = '$frequency',
                        status = '$status',
                        is_locked = $is_locked,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = '$period_id'";
        
        if ($conn->query($updateQuery)) {
            $_SESSION['flash_message'] = "Payroll period updated successfully";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error updating period: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
    }
    header("Location: periods.php");
    exit();
}

// Get record for editing if action is edit
$editRecord = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $editQuery = "SELECT id, period_name, start_date, end_date, pay_date, frequency, status, is_locked 
                  FROM payroll_periods 
                  WHERE id = '$id'";
    $editResult = $conn->query($editQuery);
    if ($editResult && $editResult->num_rows > 0) {
        $editRecord = $editResult->fetch_assoc();
        if ($editRecord['is_locked']) {
            $_SESSION['flash_message'] = "Cannot edit a locked payroll period";
            $_SESSION['flash_type'] = "danger";
            header("Location: periods.php");
            exit();
        }
    } else {
        $_SESSION['flash_message'] = "Error fetching period: " . ($editResult ? "No period found" : $conn->error);
        $_SESSION['flash_type'] = "danger";
        header("Location: periods.php");
        exit();
    }
}

// Fetch all payroll periods
$query = "SELECT id, period_name, start_date, end_date, pay_date, frequency, status, is_locked 
          FROM payroll_periods 
          ORDER BY start_date DESC";
$result = $conn->query($query);

$periodRecords = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $periodRecords[] = $row;
    }
} else {
    $_SESSION['flash_message'] = "Error fetching periods: " . $conn->error;
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
    <title>Payroll Periods - HR Management System</title>
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
            <div class="content">
                <!-- Tabs Navigation -->
                <div class="leave-tabs">
                    <a href="payroll_management.php">Payroll Management</a>
                    <a href="deductions.php">Deductions</a>
                    <a href="add_bank.php">Add Banks</a>
                    <a href="periods.php" class="active">Periods</a>
                    <a href="mp_profile.php">MP Profile</a>
                </div>

                <?php $flash = getFlashMessage(); if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <h3>Payroll Periods</h3>
                    <?php if (hasPermission('hr_manager')): ?>
                        <button class="btn btn-primary btn-sm create-btn" style="margin-bottom: 15px;">Create New Period</button>
                    <?php endif; ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Period Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Pay Date</th>
                                <th>Frequency</th>
                                <th>Status</th>
                                <th>Locked</th>
                                <?php if (hasPermission('hr_manager')): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($periodRecords)): ?>
                                <tr>
                                    <td colspan="<?php echo hasPermission('hr_manager') ? 8 : 7; ?>" class="text-center">No payroll periods found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($periodRecords as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['period_name']); ?></td>
                                    <td><?php echo formatDate($record['start_date']); ?></td>
                                    <td><?php echo formatDate($record['end_date']); ?></td>
                                    <td><?php echo formatDate($record['pay_date']); ?></td>
                                    <td><?php echo htmlspecialchars($record['frequency']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadge($record['status']); ?>">
                                            <?php echo htmlspecialchars($record['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $record['is_locked'] ? 'Yes' : 'No'; ?></td>
                                    <?php if (hasPermission('hr_manager')): ?>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-btn" 
                                                data-id="<?php echo $record['id']; ?>" 
                                                data-period-name="<?php echo htmlspecialchars($record['period_name']); ?>"
                                                data-start-date="<?php echo htmlspecialchars($record['start_date']); ?>"
                                                data-end-date="<?php echo htmlspecialchars($record['end_date']); ?>"
                                                data-pay-date="<?php echo htmlspecialchars($record['pay_date']); ?>"
                                                data-frequency="<?php echo htmlspecialchars($record['frequency']); ?>"
                                                data-status="<?php echo htmlspecialchars($record['status']); ?>"
                                                data-is-locked="<?php echo $record['is_locked'] ? '1' : '0'; ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn" 
                                                data-id="<?php echo $record['id']; ?>" 
                                                data-period-name="<?php echo htmlspecialchars($record['period_name']); ?>">
                                            Delete
                                        </button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Payroll Period</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="periods.php">
                    <div class="modal-body">
                        <input type="hidden" name="create_period" value="1">
                        
                        <div class="form-group">
                            <label class="form-label" for="create_period_name">Period Name</label>
                            <input type="text" class="form-control" id="create_period_name" name="period_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="create_start_date">Start Date</label>
                            <input type="date" class="form-control" id="create_start_date" name="start_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="create_end_date">End Date</label>
                            <input type="date" class="form-control" id="create_end_date" name="end_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="create_pay_date">Pay Date</label>
                            <input type="date" class="form-control" id="create_pay_date" name="pay_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="create_frequency">Frequency</label>
                            <select class="form-control" id="create_frequency" name="frequency" required>
                                <option value="Monthly" selected>Monthly</option>
                                <option value="Bi-weekly">Bi-weekly</option>
                                <option value="Weekly">Weekly</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="create_status">Status</label>
                            <select class="form-control" id="create_status" name="status" required>
                                <option value="Draft" selected>Draft</option>
                                <option value="Approved">Approved</option>
                                <option value="Processed">Processed</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="create_is_locked">Locked</label>
                            <input type="checkbox" id="create_is_locked" name="is_locked" value="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Period</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Payroll Period</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="periods.php">
                    <div class="modal-body">
                        <input type="hidden" name="period_id" id="edit_period_id">
                        <input type="hidden" name="update_period" value="1">
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_period_name">Period Name</label>
                            <input type="text" class="form-control" id="edit_period_name" name="period_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_start_date">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_end_date">End Date</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_pay_date">Pay Date</label>
                            <input type="date" class="form-control" id="edit_pay_date" name="pay_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_frequency">Frequency</label>
                            <select class="form-control" id="edit_frequency" name="frequency" required>
                                <option value="Monthly">Monthly</option>
                                <option value="Bi-weekly">Bi-weekly</option>
                                <option value="Weekly">Weekly</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_status">Status</label>
                            <select class="form-control" id="edit_status" name="status" required>
                                <option value="Draft">Draft</option>
                                <option value="Approved">Approved</option>
                                <option value="Processed">Processed</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_is_locked">Locked</label>
                            <input type="checkbox" id="edit_is_locked" name="is_locked" value="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Period</button>
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
                    <p>Are you sure you want to delete the payroll period <span id="delete_period_name"></span>?</p>
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
        // Handle create button click
        document.querySelectorAll('.create-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('create_period_name').value = '';
                document.getElementById('create_start_date').value = '';
                document.getElementById('create_end_date').value = '';
                document.getElementById('create_pay_date').value = '';
                document.getElementById('create_frequency').value = 'Monthly';
                document.getElementById('create_status').value = 'Draft';
                document.getElementById('create_is_locked').checked = false;
                
                document.getElementById('createModal').style.display = 'block';
            });
        });

        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const periodId = this.getAttribute('data-id');
                const periodName = this.getAttribute('data-period-name');
                const startDate = this.getAttribute('data-start-date');
                const endDate = this.getAttribute('data-end-date');
                const payDate = this.getAttribute('data-pay-date');
                const frequency = this.getAttribute('data-frequency');
                const status = this.getAttribute('data-status');
                const isLocked = this.getAttribute('data-is-locked');
                
                document.getElementById('edit_period_id').value = periodId;
                document.getElementById('edit_period_name').value = periodName;
                document.getElementById('edit_start_date').value = startDate;
                document.getElementById('edit_end_date').value = endDate;
                document.getElementById('edit_pay_date').value = payDate;
                document.getElementById('edit_frequency').value = frequency;
                document.getElementById('edit_status').value = status;
                document.getElementById('edit_is_locked').checked = isLocked === '1';
                
                document.getElementById('editModal').style.display = 'block';
            });
        });
        
        // Handle delete button clicks
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const periodId = this.getAttribute('data-id');
                const periodName = this.getAttribute('data-period-name');
                
                document.getElementById('delete_period_name').textContent = periodName;
                document.getElementById('delete_confirm_btn').href = `periods.php?action=delete&id=${periodId}`;
                
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
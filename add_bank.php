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

// Handle add action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_bank']) && hasPermission('hr_manager')) {
    $bank_name = $conn->real_escape_string($_POST['bank_name']);
    
    // Check if bank already exists
    $checkQuery = "SELECT COUNT(*) as count FROM banks WHERE bank_name = '$bank_name'";
    $checkResult = $conn->query($checkQuery);
    $bankExists = $checkResult->fetch_assoc()['count'] > 0;
    
    if ($bankExists) {
        $_SESSION['flash_message'] = "Bank already exists";
        $_SESSION['flash_type'] = "danger";
    } else {
        $insertQuery = "INSERT INTO banks (bank_name) VALUES ('$bank_name')";
        
        if ($conn->query($insertQuery)) {
            $_SESSION['flash_message'] = "Bank added successfully";
            $_SESSION['flash_type'] = "success";
            header("Location: add_bank.php");
            exit();
        } else {
            $_SESSION['flash_message'] = "Error adding bank: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $deleteQuery = "DELETE FROM banks WHERE bank_id = '$id'";
    if ($conn->query($deleteQuery)) {
        $_SESSION['flash_message'] = "Bank deleted successfully";
        $_SESSION['flash_type'] = "success";
        header("Location: add_bank.php");
        exit();
    } else {
        $_SESSION['flash_message'] = "Error deleting bank: " . $conn->error;
        $_SESSION['flash_type'] = "danger";
    }
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_bank']) && hasPermission('hr_manager')) {
    $bank_id = $conn->real_escape_string($_POST['bank_id']);
    $bank_name = $conn->real_escape_string($_POST['bank_name']);
    
    // Check if another bank already has this name
    $checkQuery = "SELECT COUNT(*) as count FROM banks WHERE bank_name = '$bank_name' AND bank_id != '$bank_id'";
    $checkResult = $conn->query($checkQuery);
    $bankExists = $checkResult->fetch_assoc()['count'] > 0;
    
    if ($bankExists) {
        $_SESSION['flash_message'] = "Another bank with this name already exists";
        $_SESSION['flash_type'] = "danger";
    } else {
        $updateQuery = "UPDATE banks SET bank_name = '$bank_name' WHERE bank_id = '$bank_id'";
        
        if ($conn->query($updateQuery)) {
            $_SESSION['flash_message'] = "Bank updated successfully";
            $_SESSION['flash_type'] = "success";
            header("Location: add_bank.php");
            exit();
        } else {
            $_SESSION['flash_message'] = "Error updating bank: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
    }
}

// Get record for editing if action is edit
$editRecord = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && hasPermission('hr_manager')) {
    $id = $conn->real_escape_string($_GET['id']);
    $editQuery = "SELECT bank_id, bank_name FROM banks WHERE bank_id = '$id'";
    $editResult = $conn->query($editQuery);
    if ($editResult && $editResult->num_rows > 0) {
        $editRecord = $editResult->fetch_assoc();
    } else {
        $_SESSION['flash_message'] = "Error fetching bank: " . ($editResult ? "No record found" : $conn->error);
        $_SESSION['flash_type'] = "danger";
        header("Location: add_bank.php");
        exit();
    }
}

// Fetch all bank data (simplified query without pagination and sorting)
$query = "SELECT bank_id, bank_name FROM banks ORDER BY bank_id ASC";
$result = $conn->query($query);

$bankRecords = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bankRecords[] = $row;
    }
} else {
    $_SESSION['flash_message'] = "Error fetching banks: " . $conn->error;
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
    <title>Banks - HR Management System</title>
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
                    <li><a href="payroll_mnagements.php">
                        <i class="fas fa-money-check"></i> Payroll
                    </a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            
            <!-- Content -->
            <div class="content">
                <?php $flash = getFlashMessage(); if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>
                

            <div class="content">
                <!-- Tabs Navigation -->
                <div class="tabs">
                    <a href="payroll_management.php">Payroll Management</a>
                    <a href="deductions.php">Deductions</a>
                    <a href="add_bank.php" class="active">Add Banks</a>
                    <a href="periods.php">Periods</a>
                    <a href="mp_profile.php">MP Profile</a>
                </div>

                <?php $flash = getFlashMessage(); if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <h3>Bank Records</h3>
                    <?php if (hasPermission('hr_manager')): ?>
                        <button class="btn btn-primary" id="addBankBtn" style="margin-bottom: 20px;">Add Bank</button>
                    <?php endif; ?>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Bank ID</th>
                                <th>Bank Name</th>
                                <?php if (hasPermission('hr_manager')): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bankRecords)): ?>
                                <tr>
                                    <td colspan="<?php echo hasPermission('hr_manager') ? 3 : 2; ?>" class="text-center">No bank records found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bankRecords as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['bank_id']); ?></td>
                                    <td><?php echo htmlspecialchars($record['bank_name']); ?></td>
                                    <?php if (hasPermission('hr_manager')): ?>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-btn" data-id="<?php echo htmlspecialchars($record['bank_id']); ?>"
                                                data-name="<?php echo htmlspecialchars($record['bank_name']); ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo htmlspecialchars($record['bank_id']); ?>" data-name="<?php echo htmlspecialchars($record['bank_name']); ?>">Delete</button>
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

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Bank</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="add_bank.php">
                    <div class="modal-body">
                        <input type="hidden" name="add_bank" value="1">
                        
                        <div class="form-group">
                            <label class="form-label" for="add_bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="add_bank_name" name="bank_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Bank</button>
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
                    <h5 class="modal-title">Edit Bank</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="add_bank.php">
                    <div class="modal-body">
                        <input type="hidden" name="update_bank" value="1">
                        <input type="hidden" name="bank_id" id="edit_bank_id">
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="edit_bank_name" name="bank_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Bank</button>
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
                    <p>Are you sure you want to delete the bank <span id="delete_bank_name"></span>?</p>
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
        // Handle add button click
        document.getElementById('addBankBtn')?.addEventListener('click', function() {
            document.getElementById('addModal').style.display = 'block';
        });

        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const bankId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('edit_bank_id').value = bankId;
                document.getElementById('edit_bank_name').value = name;
                
                document.getElementById('editModal').style.display = 'block';
            });
        });
        
        // Handle delete button clicks
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const bankId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('delete_bank_name').textContent = name;
                document.getElementById('delete_confirm_btn').href = `add_bank.php?action=delete&id=${bankId}`;
                
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
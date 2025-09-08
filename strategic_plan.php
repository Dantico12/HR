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
if (!hasPermission('hr_manager')) {
    header("Location: dashboard.php");
    exit();
}

function formatDate($date) {
    if (!$date) return 'N/A';
    return date('M d, Y', strtotime($date));
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add strategic plan
    if (isset($_POST['add_strategic_plan'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        
        $query = "INSERT INTO strategic_plan (name, start_date, end_date, created_at, updated_at) 
                  VALUES ('$name', '$start_date', '$end_date', NOW(), NOW())";
        
        if ($conn->query($query)) {
            $id = $conn->insert_id;
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $start_year = date('Y', strtotime($start_date));
                $end_year = date('Y', strtotime($end_date));
                $folder = "uploads/$start_year-$end_year/";
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $image_name = $id . '.' . $ext;
                    $target = $folder . $image_name;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        $update_query = "UPDATE strategic_plan SET image='$target' WHERE id=$id";
                        $conn->query($update_query);
                    }
                }
            }
            $_SESSION['flash_message'] = "Strategic plan added successfully";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error adding strategic plan: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
        header("Location: strategic_plan.php?tab=strategic-plans");
        exit();
    }
    
    // Update strategic plan
    if (isset($_POST['update_strategic_plan'])) {
        $id = $conn->real_escape_string($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        
        // Get old data
        $old_query = "SELECT start_date, end_date, image FROM strategic_plan WHERE id='$id'";
        $old_result = $conn->query($old_query);
        if ($old_row = $old_result->fetch_assoc()) {
            $old_start = $old_row['start_date'];
            $old_end = $old_row['end_date'];
            $old_image = $old_row['image'];
            
            $image_update = '';
            $dates_changed = ($start_date != $old_start || $end_date != $old_end);
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                // Delete old image
                if ($old_image && file_exists($old_image)) {
                    unlink($old_image);
                }
                // Upload new
                $start_year = date('Y', strtotime($start_date));
                $end_year = date('Y', strtotime($end_date));
                $folder = "uploads/$start_year-$end_year/";
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $image_name = $id . '.' . $ext;
                    $target = $folder . $image_name;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        $image_update = ", image='$target'";
                    }
                }
            } elseif ($dates_changed && $old_image) {
                // Move old image to new folder
                $old_start_year = date('Y', strtotime($old_start));
                $old_end_year = date('Y', strtotime($old_end));
                $new_start_year = date('Y', strtotime($start_date));
                $new_end_year = date('Y', strtotime($end_date));
                $new_folder = "uploads/$new_start_year-$new_end_year/";
                if (!is_dir($new_folder)) {
                    mkdir($new_folder, 0777, true);
                }
                $ext = pathinfo($old_image, PATHINFO_EXTENSION);
                $new_image_name = $id . '.' . $ext;
                $new_target = $new_folder . $new_image_name;
                if (rename($old_image, $new_target)) {
                    $image_update = ", image='$new_target'";
                }
            }
            
            $query = "UPDATE strategic_plan SET name='$name', start_date='$start_date', 
                      end_date='$end_date' $image_update, updated_at=NOW() WHERE id='$id'";
            
            if ($conn->query($query)) {
                $_SESSION['flash_message'] = "Strategic plan updated successfully";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Error updating strategic plan: " . $conn->error;
                $_SESSION['flash_type'] = "danger";
            }
        }
        header("Location: strategic_plan.php?tab=strategic-plans");
        exit();
    }
    
    // Add objective
    if (isset($_POST['add_objective'])) {
        $strategic_plan_id = $conn->real_escape_string($_POST['strategic_plan_id']);
        $name = $conn->real_escape_string($_POST['name']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        
        $query = "INSERT INTO objectives (strategic_plan_id, name, start_date, end_date, created_at, updated_at) 
                  VALUES ('$strategic_plan_id', '$name', '$start_date', '$end_date', NOW(), NOW())";
        
        if ($conn->query($query)) {
            $_SESSION['flash_message'] = "Objective added successfully";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error adding objective: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
        header("Location: strategic_plan.php?tab=objectives");
        exit();
    }
    
    // Update objective
    if (isset($_POST['update_objective'])) {
        $id = $conn->real_escape_string($_POST['id']);
        $strategic_plan_id = $conn->real_escape_string($_POST['strategic_plan_id']);
        $name = $conn->real_escape_string($_POST['name']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        
        $query = "UPDATE objectives SET strategic_plan_id='$strategic_plan_id', name='$name', 
                  start_date='$start_date', end_date='$end_date', updated_at=NOW() WHERE id='$id'";
        
        if ($conn->query($query)) {
            $_SESSION['flash_message'] = "Objective updated successfully";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error updating objective: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
        header("Location: strategic_plan.php?tab=objectives");
        exit();
    }
    
    // Add strategy
    if (isset($_POST['add_strategy'])) {
        $strategic_plan_id = $conn->real_escape_string($_POST['strategic_plan_id']);
        $objective_id = $conn->real_escape_string($_POST['objective_id']);
        $name = $conn->real_escape_string($_POST['name']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        
        $query = "INSERT INTO strategies (strategic_plan_id, objective_id, name, start_date, end_date, created_at, updated_at) 
                  VALUES ('$strategic_plan_id', '$objective_id', '$name', '$start_date', '$end_date', NOW(), NOW())";
        
        if ($conn->query($query)) {
            $_SESSION['flash_message'] = "Strategy added successfully";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error adding strategy: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
        header("Location: strategic_plan.php?tab=strategies");
        exit();
    }
    
    // Update strategy
    if (isset($_POST['update_strategy'])) {
        $id = $conn->real_escape_string($_POST['id']);
        $strategic_plan_id = $conn->real_escape_string($_POST['strategic_plan_id']);
        $objective_id = $conn->real_escape_string($_POST['objective_id']);
        $name = $conn->real_escape_string($_POST['name']);
        $start_date = $conn->real_escape_string($_POST['start_date']);
        $end_date = $conn->real_escape_string($_POST['end_date']);
        
        $query = "UPDATE strategies SET strategic_plan_id='$strategic_plan_id', objective_id='$objective_id', name='$name', 
                  start_date='$start_date', end_date='$end_date', updated_at=NOW() WHERE id='$id'";
        
        if ($conn->query($query)) {
            $_SESSION['flash_message'] = "Strategy updated successfully";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error updating strategy: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
        header("Location: strategic_plan.php?tab=strategies");
        exit();
    }
}

// Handle delete actions
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'delete_strategic_plan' && isset($_GET['id'])) {
        $id = $conn->real_escape_string($_GET['id']);
        
        // Delete image
        $image_query = "SELECT image FROM strategic_plan WHERE id = '$id'";
        $image_result = $conn->query($image_query);
        if ($image_row = $image_result->fetch_assoc()) {
            if ($image_row['image'] && file_exists($image_row['image'])) {
                unlink($image_row['image']);
            }
        }
        
        // Delete related objectives, goals, and strategies
        $conn->query("DELETE FROM objectives WHERE strategic_plan_id = '$id'");
        $conn->query("DELETE FROM strategies WHERE strategic_plan_id = '$id'");
        
        // Delete the strategic plan
        $query = "DELETE FROM strategic_plan WHERE id = '$id'";
        
        if ($conn->query($query)) {
            $_SESSION['flash_message'] = "Strategic plan and its related data deleted successfully";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error deleting strategic plan: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
        header("Location: strategic_plan.php?tab=strategic-plans");
        exit();
    }
    
    if ($_GET['action'] == 'delete_objective' && isset($_GET['id'])) {
        $id = $conn->real_escape_string($_GET['id']);
        // Update strategies to remove objective_id reference
        $conn->query("UPDATE strategies SET objective_id=NULL WHERE objective_id='$id'");
        $query = "DELETE FROM objectives WHERE id = '$id'";
        
        if ($conn->query($query)) {
            $_SESSION['flash_message'] = "Objective deleted successfully";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error deleting objective: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
        header("Location: strategic_plan.php?tab=objectives");
        exit();
    }
    
    if ($_GET['action'] == 'delete_strategy' && isset($_GET['id'])) {
        $id = $conn->real_escape_string($_GET['id']);
        $query = "DELETE FROM strategies WHERE id = '$id'";
        
        if ($conn->query($query)) {
            $_SESSION['flash_message'] = "Strategy deleted successfully";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Error deleting strategy: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
        header("Location: strategic_plan.php?tab=strategies");
        exit();
    }
}

// Fetch all strategic plans
$strategic_plans = [];
$query = "SELECT * FROM strategic_plan ORDER BY start_date DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $strategic_plans[] = $row;
    }
}

// Fetch all objectives
$objectives = [];
$query = "SELECT o.*, sp.name as strategic_plan_name 
          FROM objectives o 
          LEFT JOIN strategic_plan sp ON o.strategic_plan_id = sp.id 
          ORDER BY o.start_date DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $objectives[] = $row;
    }
}

// Fetch all strategies
$strategies = [];
$query = "SELECT s.*, sp.name as strategic_plan_name, o.name as objective_name 
          FROM strategies s 
          LEFT JOIN strategic_plan sp ON s.strategic_plan_id = sp.id 
          LEFT JOIN objectives o ON s.objective_id = o.id 
          ORDER BY s.start_date DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $strategies[] = $row;
    }
}

$conn->close();

// Get strategic plans for dropdown
$strategic_plans_dropdown = [];
foreach ($strategic_plans as $plan) {
    $strategic_plans_dropdown[$plan['id']] = $plan['name'];
}

// Get objectives for dropdown
$objectives_dropdown = [];
foreach ($objectives as $objective) {
    $objectives_dropdown[$objective['id']] = $objective['name'];
}

// Get flash message if exists
$flash_message = '';
$flash_type = '';
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    $flash_type = $_SESSION['flash_type'];
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strategic Plan - HR Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles for Strategic Plan page */
        .card {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--border-accent);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .card-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }
        
        .empty-state p {
            margin-bottom: 1.5rem;
            color: var(--text-muted);
        }
        
        .tabs {
            display: flex;
            background: var(--bg-glass);
            border-radius: 12px;
            padding: 0.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            backdrop-filter: blur(20px);
            overflow-x: auto;
            gap: 0.5rem;
        }
        
        .tabs ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        
        .tab-link {
            flex: 1;
            min-width: 150px;
            padding: 0.75rem 1.5rem;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: 8px;
            transition: var(--transition);
            text-align: center;
            white-space: nowrap;
            position: relative;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
        }
        
        .tab-link:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--border-color);
        }
        
        .tab-link.active {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
            border-color: var(--primary-color);
        }
        
        .tab-link.active::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
            z-index: -1;
            opacity: 0.3;
            filter: blur(4px);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Modal adjustments */
        .modal-content {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 0;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.3s ease-out;
        }
        
        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-glass);
        }
        
        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        .modal form {
            padding: 2rem;
            max-height: calc(90vh - 120px);
            overflow-y: auto;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        /* Styles for plan grid in goals tab */
        .plan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .plan-card {
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }
        
        .plan-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .plan-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid var(--border-color);
        }
        
        .plan-info {
            padding: 1rem;
        }
        
        .plan-info h4 {
            margin: 0 0 0.5rem;
            font-size: 1.25rem;
            color: var(--text-primary);
        }
        
        .plan-info p {
            margin: 0.25rem 0;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .preview-image {
            max-width: 100%;
            height: auto;
            margin-top: 0.5rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .tab-link {
                min-width: 120px;
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
                    <li><a href="dashboard.php">
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
                    <li><a href="strategic_plan.php?tab=goals">
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
            <div class="content">
                <div class="leave-tabs">
                    <a href="strategic_plan.php?tab=goals" class="leave-tab">Strategic Plan</a>
                    <a href="employee_appraisal.php" class="leave-tab">Employee Appraisal</a>
                    <?php if (in_array($user['role'], ['hr_manager', 'super_admin', 'manager', 'managing_director', 'section_head', 'dept_head'])): ?>
                        <a href="performance_appraisal.php" class="leave-tab">Performance Appraisal</a>
                    <?php endif; ?>
                    <?php if (in_array($user['role'], ['hr_manager', 'super_admin', 'manager', 'managing_director'])): ?>
                        <a href="appraisal_management.php" class="leave-tab">Appraisal Management</a>
                    <?php endif; ?>
                    <a href="completed_appraisals.php" class="leave-tab">Completed Appraisals</a>
                </div>
                
                <?php if ($flash_message): ?>
                <div class="alert alert-<?php echo $flash_type; ?>">
                    <?php echo $flash_message; ?>
                </div>
                <?php endif; ?>
                
                <div class="tabs">
                    <ul>
                        <li><a href="#" class="tab-link active" data-tab="goals">Goals</a></li>
                        <?php if (hasPermission('hr_manager')): ?>
                            <li><a href="#" class="tab-link" data-tab="strategic-plans">Strategic Plan</a></li>
                        <?php endif; ?>
                        <li><a href="#" class="tab-link" data-tab="objectives">Objectives</a></li>
                        <?php if (hasPermission('hr_manager')): ?>
                            <li><a href="#" class="tab-link" data-tab="strategies">Strategies</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Goals Tab (Display Strategic Plans with Images) -->
                <div id="goals" class="tab-content active">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Strategic Plans</h3>
                        </div>
                        <?php if (empty($strategic_plans)): ?>
                        <div class="empty-state">
                            <i class="fas fa-chess-board"></i>
                            <h3>No Strategic Plans Found</h3>
                            <p>Get started by adding your first strategic plan in the Strategic Plans tab.</p>
                        </div>
                        <?php else: ?>
                        <div class="plan-grid">
                            <?php foreach ($strategic_plans as $plan): ?>
                            <div class="plan-card">
                                <img class="plan-image" src="<?php echo htmlspecialchars($plan['image'] ?? 'assets/default-plan.jpg'); ?>" alt="<?php echo htmlspecialchars($plan['name']); ?>">
                                <div class="plan-info">
                                    <h4><?php echo htmlspecialchars($plan['name']); ?></h4>
                                    <p>Start: <?php echo formatDate($plan['start_date']); ?></p>
                                    <p>End: <?php echo formatDate($plan['end_date']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Strategic Plans Tab -->
                <div id="strategic-plans" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Strategic Plans</h3>
                            <button class="btn btn-primary" onclick="openModal('addStrategicPlanModal')">
                                <i class="fas fa-plus"></i> Add Strategic Plan
                            </button>
                        </div>
                        <?php if (empty($strategic_plans)): ?>
                        <div class="empty-state">
                            <i class="fas fa-chess-board"></i>
                            <h3>No Strategic Plans Found</h3>
                            <p>Get started by adding your first strategic plan</p>
                            <button class="btn btn-primary mt-3" onclick="openModal('addStrategicPlanModal')">
                                <i class="fas fa-plus"></i> Add Strategic Plan
                            </button>
                        </div>
                        <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Image</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($strategic_plans as $plan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($plan['name']); ?></td>
                                    <td><?php echo formatDate($plan['start_date']); ?></td>
                                    <td><?php echo formatDate($plan['end_date']); ?></td>
                                    <td>
                                        <?php if (isset($plan['image']) && $plan['image']): ?>
                                        <img src="<?php echo htmlspecialchars($plan['image']); ?>" alt="Plan Image" width="50" height="50" style="border-radius: 4px;">
                                        <?php else: ?>
                                        N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($plan['created_at']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="editStrategicPlan(<?php echo $plan['id']; ?>, '<?php echo addslashes(htmlspecialchars($plan['name'])); ?>', '<?php echo $plan['start_date']; ?>', '<?php echo $plan['end_date']; ?>', '<?php echo addslashes(htmlspecialchars($plan['image'] ?? '')); ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="strategic_plan.php?action=delete_strategic_plan&id=<?php echo $plan['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this strategic plan and all its related data?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Objectives Tab -->
                <div id="objectives" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Objectives</h3>
                            <button class="btn btn-primary" onclick="openModal('addObjectiveModal')">
                                <i class="fas fa-plus"></i> Add Objective
                            </button>
                        </div>
                        <?php if (empty($objectives)): ?>
                        <div class="empty-state">
                            <i class="fas fa-bullseye"></i>
                            <h3>No Objectives Found</h3>
                            <p>Get started by adding your first objective</p>
                            <button class="btn btn-primary mt-3" onclick="openModal('addObjectiveModal')">
                                <i class="fas fa-plus"></i> Add Objective
                            </button>
                        </div>
                        <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    
                                    <th>Strategic Plan</th>
                                    <th>Objectives</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($objectives as $objective): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($objective['strategic_plan_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($objective['name']); ?></td>
                                    <td><?php echo formatDate($objective['start_date']); ?></td>
                                    <td><?php echo formatDate($objective['end_date']); ?></td>
                                    <td><?php echo formatDate($objective['created_at']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="editObjective(<?php echo $objective['id']; ?>, <?php echo $objective['strategic_plan_id']; ?>, '<?php echo htmlspecialchars($objective['name']); ?>', '<?php echo $objective['start_date']; ?>', '<?php echo $objective['end_date']; ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="strategic_plan.php?action=delete_objective&id=<?php echo $objective['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this objective?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Strategies Tab -->
                <?php if (hasPermission('hr_manager')): ?>
                <div id="strategies" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Strategies</h3>
                            <button class="btn btn-primary" onclick="openModal('addStrategyModal')">
                                <i class="fas fa-plus"></i> Add Strategy
                            </button>
                        </div>
                        <?php if (empty($strategies)): ?>
                        <div class="empty-state">
                            <i class="fas fa-lightbulb"></i>
                            <h3>No Strategies Found</h3>
                            <p>Get started by adding your first strategy</p>
                            <button class="btn btn-primary mt-3" onclick="openModal('addStrategyModal')">
                                <i class="fas fa-plus"></i> Add Strategy
                            </button>
                        </div>
                        <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                  
                                    <th>Strategic Plan</th>
                                    <th>Objective</th>
                                      <th>Strategy</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($strategies as $strategy): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($strategy['strategic_plan_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($strategy['objective_name'] ?? 'N/A'); ?></td>
                                     <td><?php echo htmlspecialchars($strategy['name']); ?></td>
                                    <td><?php echo formatDate($strategy['start_date']); ?></td>
                                    <td><?php echo formatDate($strategy['end_date']); ?></td>
                                    <td><?php echo formatDate($strategy['created_at']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="editStrategy(<?php echo $strategy['id']; ?>, <?php echo $strategy['strategic_plan_id']; ?>, <?php echo $strategy['objective_id'] ?? 'null'; ?>, '<?php echo htmlspecialchars($strategy['name']); ?>', '<?php echo $strategy['start_date']; ?>', '<?php echo $strategy['end_date']; ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="strategic_plan.php?action=delete_strategy&id=<?php echo $strategy['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this strategy?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Strategic Plan Modal -->
    <div id="addStrategicPlanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Strategic Plan</h3>
                <button class="close" onclick="closeModal('addStrategicPlanModal')">&times;</button>
            </div>
            <form method="POST" action="strategic_plan.php" enctype="multipart/form-data">
                <input type="hidden" name="add_strategic_plan" value="1">
                <div class="form-group">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="image">Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addStrategicPlanModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Strategic Plan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Strategic Plan Modal -->
    <div id="editStrategicPlanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Strategic Plan</h3>
                <button class="close" onclick="closeModal('editStrategicPlanModal')">&times;</button>
            </div>
            <form method="POST" action="strategic_plan.php" enctype="multipart/form-data">
                <input type="hidden" name="update_strategic_plan" value="1">
                <input type="hidden" id="edit_strategic_plan_id" name="id">
                <div class="form-group">
                    <label class="form-label" for="edit_name">Name</label>
                    <input type="text" class="form-control" id="edit_name" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_start_date">Start Date</label>
                        <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_end_date">End Date</label>
                        <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Current Image</label>
                    <img id="edit_image_preview" src="" alt="Current Image" class="preview-image" style="display: none;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_image">New Image (optional)</label>
                    <input type="file" class="form-control" id="edit_image" name="image" accept="image/jpeg,image/png,image/gif">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editStrategicPlanModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Strategic Plan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Objective Modal -->
    <div id="addObjectiveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Objective</h3>
                <button class="close" onclick="closeModal('addObjectiveModal')">&times;</button>
            </div>
            <form method="POST" action="strategic_plan.php">
                <input type="hidden" name="add_objective" value="1">
                <div class="form-group">
                    <label class="form-label" for="strategic_plan_id">Strategic Plan</label>
                    <select class="form-control" id="strategic_plan_id" name="strategic_plan_id" required>
                        <option value="">Select Strategic Plan</option>
                        <?php foreach ($strategic_plans_dropdown as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="obj_name">Name</label>
                    <input type="text" class="form-control" id="obj_name" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="obj_start_date">Start Date</label>
                        <input type="date" class="form-control" id="obj_start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="obj_end_date">End Date</label>
                        <input type="date" class="form-control" id="obj_end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addObjectiveModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Objective</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Objective Modal -->
    <div id="editObjectiveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Objective</h3>
                <button class="close" onclick="closeModal('editObjectiveModal')">&times;</button>
            </div>
            <form method="POST" action="strategic_plan.php">
                <input type="hidden" name="update_objective" value="1">
                <input type="hidden" id="edit_objective_id" name="id">
                <div class="form-group">
                    <label class="form-label" for="edit_obj_strategic_plan_id">Strategic Plan</label>
                    <select class="form-control" id="edit_obj_strategic_plan_id" name="strategic_plan_id" required>
                        <option value="">Select Strategic Plan</option>
                        <?php foreach ($strategic_plans_dropdown as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_obj_name">Name</label>
                    <input type="text" class="form-control" id="edit_obj_name" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_obj_start_date">Start Date</label>
                        <input type="date" class="form-control" id="edit_obj_start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_obj_end_date">End Date</label>
                        <input type="date" class="form-control" id="edit_obj_end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editObjectiveModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Objective</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Goal Modal -->
    <div id="addGoalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Goal</h3>
                <button class="close" onclick="closeModal('addGoalModal')">&times;</button>
            </div>
            <form method="POST" action="strategic_plan.php">
                <input type="hidden" name="add_goal" value="1">
                <div class="form-group">
                    <label class="form-label" for="goal_strategic_plan_id">Strategic Plan</label>
                    <select class="form-control" id="goal_strategic_plan_id" name="strategic_plan_id" required>
                        <option value="">Select Strategic Plan</option>
                        <?php foreach ($strategic_plans_dropdown as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="goal_name">Name</label>
                    <input type="text" class="form-control" id="goal_name" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="goal_start_date">Start Date</label>
                        <input type="date" class="form-control" id="goal_start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="goal_end_date">End Date</label>
                        <input type="date" class="form-control" id="goal_end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addGoalModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Goal</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Goal Modal -->
    <div id="editGoalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Goal</h3>
                <button class="close" onclick="closeModal('editGoalModal')">&times;</button>
            </div>
            <form method="POST" action="strategic_plan.php">
                <input type="hidden" name="update_goal" value="1">
                <input type="hidden" id="edit_goal_id" name="id">
                <div class="form-group">
                    <label class="form-label" for="edit_goal_strategic_plan_id">Strategic Plan</label>
                    <select class="form-control" id="edit_goal_strategic_plan_id" name="strategic_plan_id" required>
                        <option value="">Select Strategic Plan</option>
                        <?php foreach ($strategic_plans_dropdown as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_goal_name">Name</label>
                    <input type="text" class="form-control" id="edit_goal_name" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_goal_start_date">Start Date</label>
                        <input type="date" class="form-control" id="edit_goal_start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_goal_end_date">End Date</label>
                        <input type="date" class="form-control" id="edit_goal_end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editGoalModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Goal</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Strategy Modal -->
    <div id="addStrategyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Strategy</h3>
                <button class="close" onclick="closeModal('addStrategyModal')">&times;</button>
            </div>
            <form method="POST" action="strategic_plan.php">
                <input type="hidden" name="add_strategy" value="1">
                <div class="form-group">
                    <label class="form-label" for="strategy_strategic_plan_id">Strategic Plan</label>
                    <select class="form-control" id="strategy_strategic_plan_id" name="strategic_plan_id" required>
                        <option value="">Select Strategic Plan</option>
                        <?php foreach ($strategic_plans_dropdown as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="strategy_objective_id">Objective</label>
                    <select class="form-control" id="strategy_objective_id" name="objective_id" required>
                        <option value="">Select Objective</option>
                        <?php foreach ($objectives_dropdown as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="strategy_name">Name</label>
                    <input type="text" class="form-control" id="strategy_name" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="strategy_start_date">Start Date</label>
                        <input type="date" class="form-control" id="strategy_start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="strategy_end_date">End Date</label>
                        <input type="date" class="form-control" id="strategy_end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addStrategyModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Strategy</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Strategy Modal -->
    <div id="editStrategyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Strategy</h3>
                <button class="close" onclick="closeModal('editStrategyModal')">&times;</button>
            </div>
            <form method="POST" action="strategic_plan.php">
                <input type="hidden" name="update_strategy" value="1">
                <input type="hidden" id="edit_strategy_id" name="id">
                <div class="form-group">
                    <label class="form-label" for="edit_strategy_strategic_plan_id">Strategic Plan</label>
                    <select class="form-control" id="edit_strategy_strategic_plan_id" name="strategic_plan_id" required>
                        <option value="">Select Strategic Plan</option>
                        <?php foreach ($strategic_plans_dropdown as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_strategy_objective_id">Objective</label>
                    <select class="form-control" id="edit_strategy_objective_id" name="objective_id" required>
                        <option value="">Select Objective</option>
                        <?php foreach ($objectives_dropdown as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_strategy_name">Name</label>
                    <input type="text" class="form-control" id="edit_strategy_name" name="name" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_strategy_start_date">Start Date</label>
                        <input type="date" class="form-control" id="edit_strategy_start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_strategy_end_date">End Date</label>
                        <input type="date" class="form-control" id="edit_strategy_end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editStrategyModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Strategy</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching function
        function showTab(tabId) {
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active class from all tabs
            document.querySelectorAll('.tab-link').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content and mark tab as active
            const tabContent = document.getElementById(tabId);
            if (tabContent) {
                tabContent.classList.add('active');
                const tabLink = document.querySelector(`.tab-link[data-tab="${tabId}"]`);
                if (tabLink) {
                    tabLink.classList.add('active');
                }
            }
        }

        // Add event listeners to tabs
        document.querySelectorAll('.tab-link').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default anchor behavior
                showTab(tab.getAttribute('data-tab'));
            });
        });

        // Set active tab based on URL query parameter
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'goals'; // Default to goals
            if (document.getElementById(activeTab)) {
                showTab(activeTab);
            }
        });

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function editStrategicPlan(id, name, startDate, endDate, image) {
            document.getElementById('edit_strategic_plan_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_start_date').value = startDate;
            document.getElementById('edit_end_date').value = endDate;
            const preview = document.getElementById('edit_image_preview');
            if (image) {
                preview.src = image;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
            openModal('editStrategicPlanModal');
        }

        function editObjective(id, strategicPlanId, name, startDate, endDate) {
            document.getElementById('edit_objective_id').value = id;
            document.getElementById('edit_obj_strategic_plan_id').value = strategicPlanId;
            document.getElementById('edit_obj_name').value = name;
            document.getElementById('edit_obj_start_date').value = startDate;
            document.getElementById('edit_obj_end_date').value = endDate;
            openModal('editObjectiveModal');
        }

        function editGoal(id, strategicPlanId, name, startDate, endDate) {
            document.getElementById('edit_goal_id').value = id;
            document.getElementById('edit_goal_strategic_plan_id').value = strategicPlanId;
            document.getElementById('edit_goal_name').value = name;
            document.getElementById('edit_goal_start_date').value = startDate;
            document.getElementById('edit_goal_end_date').value = endDate;
            openModal('editGoalModal');
        }

        function editStrategy(id, strategicPlanId, objectiveId, name, startDate, endDate) {
            document.getElementById('edit_strategy_id').value = id;
            document.getElementById('edit_strategy_strategic_plan_id').value = strategicPlanId;
            document.getElementById('edit_strategy_objective_id').value = objectiveId || '';
            document.getElementById('edit_strategy_name').value = name;
            document.getElementById('edit_strategy_start_date').value = startDate;
            document.getElementById('edit_strategy_end_date').value = endDate;
            openModal('editStrategyModal');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    </script>
</body>
</html>
<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle theme toggle
if (isset($_POST['toggle_theme'])) {
    $currentTheme = $_SESSION['theme'] ?? 'light';
    $_SESSION['theme'] = ($currentTheme === 'light') ? 'dark' : 'light';
    
    // Redirect back to the current page to refresh with new theme
    $redirectUrl = $_SERVER['REQUEST_URI'];
    header("Location: $redirectUrl");
    exit();
}

// Get current theme (default to light)
$currentTheme = $_SESSION['theme'] ?? 'light';

// Get user info from session
$user = [
    'first_name' => isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'User',
    'last_name' => isset($_SESSION['user_name']) ? (explode(' ', $_SESSION['user_name'])[1] ?? '') : '',
    'role' => $_SESSION['user_role'] ?? 'guest',
    'id' => $_SESSION['user_id'] ?? null
];
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $currentTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    
    <style>
    /* Theme Variables - Only for light theme since your CSS is dark by default */
    :root[data-theme="light"] {
        --primary-color: #007bff;
        --primary-dark: #0056b3;
        --secondary-color: #6f42c1;
        --accent-color: #dc3545;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --error-color: #dc3545;
        
        --bg-primary: #ffffff;
        --bg-secondary: #f8f9fa;
        --bg-tertiary: #e9ecef;
        --bg-card: rgba(255, 255, 255, 0.9);
        --bg-glass: rgba(255, 255, 255, 0.1);
        
        --text-primary: #212529;
        --text-secondary: #6c757d;
        --text-muted: #adb5bd;
        
        --border-color: rgba(0, 0, 0, 0.125);
        --border-accent: rgba(0, 123, 255, 0.3);
        
        --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        --shadow-glow: 0 0 20px rgba(0, 123, 255, 0.2);
    }

    /* Light theme body background override */
    :root[data-theme="light"] body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #dee2e6 100%);
        color: var(--text-primary);
    }

    :root[data-theme="light"] body::before {
        background: 
            radial-gradient(circle at 20% 80%, rgba(0, 123, 255, 0.03) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(111, 66, 193, 0.03) 0%, transparent 50%);
    }

    /* Theme Toggle Switch Styles */
    .theme-toggle {
        position: relative;
        display: flex;
        align-items: center;
        margin-right: 1rem;
    }

    .theme-switch {
        position: relative;
        width: 60px;
        height: 30px;
        background: var(--bg-tertiary);
        border-radius: 15px;
        border: 2px solid var(--border-color);
        cursor: pointer;
        transition: all 0.3s ease;
        outline: none;
        overflow: hidden;
    }

    .theme-switch:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary-color);
    }

    .theme-slider {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 22px;
        height: 22px;
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        box-shadow: var(--shadow-sm);
    }

    :root[data-theme="dark"] .theme-slider {
        transform: translateX(28px);
    }

    .theme-icon {
        transition: all 0.3s ease;
        filter: drop-shadow(0 0 2px rgba(0,0,0,0.3));
    }

    /* Header Integration */
    .main-header {
        background: var(--bg-card);
        color: var(--text-primary);
        padding: 1rem 2rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--shadow-sm);
        backdrop-filter: blur(10px);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-primary);
    }

    .user-name {
        font-weight: 500;
        color: var(--text-primary);
    }

    .role-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: 1px solid var(--primary-color);
    }

    .logout-btn {
        background: linear-gradient(45deg, var(--error-color), #c82333);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .logout-btn:hover {
        background: linear-gradient(45deg, #c82333, var(--error-color));
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        text-decoration: none;
        color: white;
    }

    .page-title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .sidebar-toggle {
        background: none;
        border: none;
        color: var(--text-primary);
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        display: none;
    }

    .sidebar-toggle:hover {
        background: var(--bg-glass);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .main-header {
            padding: 1rem;
        }
        
        .header-right {
            gap: 0.5rem;
        }
        
        .user-name {
            display: none;
        }
        
        .theme-toggle {
            margin-right: 0.5rem;
        }

        .sidebar-toggle {
            display: block;
        }
    }
    </style>
</head>

<div class="main-header">
    <div class="header-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
    </div>
    
    <div class="header-right">
        <!-- Theme Toggle -->
        <div class="theme-toggle">
            <form method="POST" style="margin: 0;">
                <button type="submit" name="toggle_theme" class="theme-switch">
                    <div class="theme-slider">
                        <span class="theme-icon">
                            <?php if ($currentTheme === 'light'): ?>
                                🌞
                            <?php else: ?>
                                🌙
                            <?php endif; ?>
                        </span>
                    </div>
                </button>
            </form>
        </div>
        
        <!-- User Info -->
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
            <span class="role-badge"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $user['role']))); ?></span>
        </div>
        
        <!-- Logout Button -->
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-open');
    }
}
</script>
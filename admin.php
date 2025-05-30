<?php
require_once 'config.php';
require_once 'check_admin.php';

// Debug information
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is admin
check_admin();

// Get admin user data
try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    
    // Get admin's email from session
    $admin_email = $_SESSION['email'];
    
    // Get admin's last login time
    $stmt = $db->prepare("SELECT last_login FROM admins WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $admin_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin_data = $result->fetch_assoc();
        $last_login = $admin_data['last_login'] ? date('F d, Y \a\t H:i', strtotime($admin_data['last_login'])) : 'First Login';
    } else {
        $last_login = 'Not Available';
    }
    
    // Get total number of users (excluding admins)
    $users_count = 0;
    $user_query = "SELECT COUNT(*) as count FROM users WHERE role = 'User'";
    $user_result = mysqli_query($db, $user_query);
    if ($user_result) {
        $row = mysqli_fetch_assoc($user_result);
        $users_count = $row['count'];
    }

    // Get total number of messages
    $messages_count = 0;
    $message_query = "SELECT COUNT(*) as count FROM messages";
    $message_result = mysqli_query($db, $message_query);
    if ($message_result) {
        $row = mysqli_fetch_assoc($message_result);
        $messages_count = $row['count'];
    }
    
    mysqli_close($db);
} catch (Exception $e) {
    error_log("Admin panel error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while loading admin panel";
    header("Location: login.php");
    exit;
}

// Get login times from session
$current_login = $_SESSION['current_login'] ?? date('F d, Y \a\t H:i');
$last_login = $_SESSION['last_login'] ?? 'Not Available';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Masked Intel</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
            min-height: 100vh;
            color: white;
        }

        .top-nav {
            background: rgba(41, 51, 92, 0.9);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-link.active {
            background: #1a73e8;
        }

        .nav-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.1);
        }

        .login-times {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            margin: 2rem 0;
            font-size: 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .stats-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 2rem auto;
            max-width: 1000px;
        }

        .stat-card {
            background: rgba(41, 98, 255, 0.9);
            border-radius: 10px;
            padding: 2rem;
            width: 300px;
            text-align: center;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: white;
        }

        .stat-label {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: white;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: white;
        }

        .stat-trend {
            font-size: 0.9rem;
        }

        .stat-trend.positive {
            color: #4caf50;
        }

        .stat-trend.negative {
            color: #f44336;
        }

        .quick-actions {
            background: rgba(22, 28, 51, 0.8);
            border-radius: 10px;
            padding: 2rem;
            max-width: 1000px;
            margin: 2rem auto;
        }

        .quick-actions h2 {
            color: #64b5f6;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .action-btn {
            background: #1a73e8;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #1557b0;
            transform: translateY(-2px);
        }

        .action-btn i {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <a href="#" class="logo">
            <img src="logo.png" alt="Masked Intel Logo">
            Masked Intel
        </a>
        <div class="nav-links">
            <a href="admin.php" class="nav-link active">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                Users
            </a>
            <a href="messages.php" class="nav-link">
                <i class="fas fa-envelope"></i>
                Messages
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <div class="login-times">
        <div>Current login: <?php echo htmlspecialchars($current_login); ?></div>
        <div>Last login: <?php echo htmlspecialchars($last_login); ?></div>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-label">Total Users</div>
            <div class="stat-value"><?php echo number_format($users_count); ?></div>
            <div class="stat-trend positive">+<?php echo $growth_percentage; ?>% this week</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-label">Messages</div>
            <div class="stat-value"><?php echo number_format($messages_count); ?></div>
            <div class="stat-trend negative">-5% this week</div>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="add_user.php" class="action-btn">
                <i class="fas fa-user-plus"></i>
                Add New User
            </a>
            <a href="messages.php" class="action-btn">
                <i class="fas fa-envelope"></i>
                View Messages
            </a>
        </div>
    </div>
</body>
</html>
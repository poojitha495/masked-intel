<?php
require_once 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Database connection
$db = mysqli_connect("localhost", "root", "", "project");

// Get all users
$query = "SELECT id, email, user_type FROM users WHERE user_type = 'user'";
$result = mysqli_query($db, $query);

// Add this at the top of connect.php after database connection to debug
if (isset($_POST['user_login'])) {
    echo "Attempting user login...<br>";
    echo "Email: " . $_POST['email'] . "<br>";
    // Don't echo password in production, this is just for debugging
    echo "Password: " . $_POST['password'] . "<br>";
    
    // Check if we can query the database
    $test_query = "SELECT * FROM users";
    $test_result = mysqli_query($db, $test_query);
    if ($test_result) {
        echo "Database connection successful. Found " . mysqli_num_rows($test_result) . " users.<br>";
    } else {
        echo "Database query failed: " . mysqli_error($db) . "<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users - Masked Intel</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .users-table th {
            background: rgba(255, 255, 255, 0.1);
            font-weight: bold;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registered Users</h1>
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>User Type</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html> 
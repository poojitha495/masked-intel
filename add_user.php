<?php
require_once 'config.php';
require_once 'check_admin.php';

// Check if user is admin
check_admin();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password'])) {
            throw new Exception('All fields are required');
        }

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = 'User'; // Default role
        $status = isset($_POST['status']) ? 'active' : 'inactive';

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Validate password length
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long');
        }

        $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$db) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }

        // Check if email already exists
        $check_stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Email already exists');
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $status);
        
        if ($stmt->execute()) {
            $success_message = 'User added successfully!';
        } else {
            throw new Exception('Failed to add user');
        }

        mysqli_close($db);
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Masked Intel</title>
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

        .main-content {
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        h1 {
            color: #64b5f6;
            font-size: 2rem;
            font-weight: normal;
            margin-bottom: 2rem;
        }

        .form-card {
            background: rgba(22, 28, 51, 0.8);
            border-radius: 10px;
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #64b5f6;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #1a73e8;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .submit-btn {
            background: #1a73e8;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background: #1557b0;
        }

        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .success {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .error {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
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
            <a href="admin.php" class="nav-link">
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

    <div class="main-content">
        <h1>Add New User</h1>

        <?php if ($success_message): ?>
        <div class="message success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="status" name="status" checked>
                    <label for="status">Active Account</label>
                </div>

                <button type="submit" class="submit-btn">Add User</button>
            </form>
        </div>
    </div>
</body>
</html> 
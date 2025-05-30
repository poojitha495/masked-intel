<?php
require_once 'config.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Initialize variables
$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = 'User'; // Default role for new registrations

        // Validate name
        if (empty($name)) {
            throw new Exception('Name is required');
        }
        if (strlen($name) < 2 || strlen($name) > 50) {
            throw new Exception('Name must be between 2 and 50 characters');
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Validate password
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long');
        }

        // Check password confirmation
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }

        // Connect to database
        $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$db) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }

        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Email already exists');
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $insert_stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
        if (!$insert_stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }

        $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to create user account");
        }

        $success = "Registration successful! Please login.";
        header("Location: login.php");
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Masked Intel</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .register-container {
            max-width: 400px;
            margin: 3rem auto;
            padding: 2rem;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.1) 100%);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.2);
        }

        .register-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .error-message {
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
            background: rgba(255, 107, 107, 0.1);
        }

        .success-message {
            color: #4caf50;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
            background: rgba(76, 175, 80, 0.1);
        }

        h1 {
            text-align: center;
            color: #fff;
            margin-bottom: 2rem;
        }

        label {
            color: #fff;
            font-weight: 600;
        }

        input, select {
            padding: 0.8rem 1rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #4a90e2;
        }

        button {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .login-link {
            text-align: center;
            margin-top: 1rem;
            color: #fff;
        }

        .login-link a {
            color: #4a90e2;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="geometric-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="register-container">
        <h1>Register</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form class="register-form" method="POST" action="register.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required placeholder="Enter your full name" minlength="2" maxlength="50">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password" minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
            </div>

            <button type="submit">Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html> 
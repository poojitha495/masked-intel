<?php
require_once 'config.php';

// Initialize errors array
$errors = array();

// Database connection with error handling
try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    array_push($errors, "Database connection error. Please try again later.");
    $_SESSION['errors'] = $errors;
    header('location: login.php');
    exit();
}

// Initialize CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF Protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        array_push($errors, "Invalid request");
        $_SESSION['errors'] = $errors;
        header('location: login.php');
        exit();
    }
}

// Handle User Login
if (isset($_POST['user_login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Debug information
    echo "Login attempt:<br>";
    echo "Email: " . $email . "<br>";
    
    // Check if user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // For debugging only - remove in production
        echo "User found in database<br>";
        echo "Stored password: " . $user['password'] . "<br>";
        echo "Entered password: " . $password . "<br>";
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['success'] = "Login successful!";
            header('location: dashboard.php');
            exit();
        } else {
            array_push($errors, "Wrong password");
        }
    } else {
        array_push($errors, "User account not found");
    }
}

// Handle User Registration
if (isset($_POST['register'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        array_push($errors, "Passwords do not match");
        $_SESSION['errors'] = $errors;
        header('location: register.php');
        exit();
    }
    
    // Validate password strength
    if (strlen($password) < 6) {
        array_push($errors, "Password must be at least 6 characters long");
        $_SESSION['errors'] = $errors;
        header('location: register.php');
        exit();
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        array_push($errors, "Email already exists");
    } else {
        // Hash password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $db->prepare("INSERT INTO users (email, password, user_type) VALUES (?, ?, 'user')");
        $stmt->bind_param("ss", $email, $hashed_password);
        
        if ($stmt->execute()) {
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = 'user';
            $_SESSION['success'] = "Registration successful!";
            header('location: dashboard.php');
            exit();
        } else {
            array_push($errors, "Registration failed: " . $db->error);
        }
    }
}

// If there are errors, redirect back with errors
if (count($errors) > 0) {
    $_SESSION['errors'] = $errors;
    header('location: login.php');
    exit();
}

mysqli_close($db);
?>
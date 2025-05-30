<?php
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log the request method and POST data
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST Data: " . print_r($_POST, true));

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['errors'] = ['Please login to submit a message'];
    redirect('login.php');
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['errors'] = ['Invalid request method'];
    redirect('dashboard.php');
}

// Validate form data
$required_fields = ['name', 'email', 'inquiry_type', 'message'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = ucfirst($field) . ' is required';
        error_log("Missing required field: " . $field);
    }
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    redirect('dashboard.php');
}

// Sanitize and get form data
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
$inquiry_type = filter_input(INPUT_POST, 'inquiry_type', FILTER_SANITIZE_STRING);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

// Debug: Log sanitized data
error_log("Sanitized Data:");
error_log("Name: " . $name);
error_log("Email: " . $email);
error_log("Phone: " . $phone);
error_log("Inquiry Type: " . $inquiry_type);
error_log("Message: " . $message);

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Check if messages table exists, create if it doesn't
    $table_check = mysqli_query($db, "SHOW TABLES LIKE 'messages'");
    if (mysqli_num_rows($table_check) === 0) {
        error_log("Creating messages table...");
        $create_table_query = "CREATE TABLE messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            inquiry_type VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (email),
            INDEX (created_at)
        )";
        
        if (!mysqli_query($db, $create_table_query)) {
            throw new Exception("Failed to create messages table: " . mysqli_error($db));
        }
        error_log("Messages table created successfully");
    }

    // Insert the message
    $query = "INSERT INTO messages (name, email, phone, inquiry_type, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $db->error);
    }

    $stmt->bind_param("sssss", $name, $email, $phone, $inquiry_type, $message);
    
    error_log("Executing insert query...");
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert message: " . $stmt->error);
    }
    error_log("Message inserted successfully with ID: " . $stmt->insert_id);

    $_SESSION['success'] = 'Your message has been sent successfully!';
    mysqli_close($db);
    redirect('dashboard.php');

} catch (Exception $e) {
    error_log("Message submission error: " . $e->getMessage());
    $_SESSION['errors'] = ['An error occurred while sending your message. Please try again later.'];
    redirect('dashboard.php');
}
?> 
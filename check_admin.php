<?php
require_once 'config.php';

// Check if user is logged in and is an admin
function check_admin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        header("Location: login.php");
        exit;
    }
}

// Function to check if email is admin
function is_admin_email($email) {
    try {
        $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$db) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }
        
        $stmt = $db->prepare("SELECT role FROM users WHERE email = ? AND role = 'Admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
        
    } catch (Exception $e) {
        error_log("Error checking admin email: " . $e->getMessage());
        return false;
    } finally {
        if (isset($db)) {
            mysqli_close($db);
        }
    }
}
?> 
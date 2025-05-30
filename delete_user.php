<?php
require_once 'config.php';
require_once 'check_admin.php';

// Check if user is admin
check_admin();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('User ID is required');
    }

    $user_id = intval($_GET['id']);

    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Prepare and execute delete query
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    if ($stmt === false) {
        throw new Exception("Failed to prepare delete query: " . $db->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete user: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("User not found");
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 
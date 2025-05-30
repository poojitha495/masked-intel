<?php
require_once 'config.php';
require_once 'check_admin.php';

// Check if user is admin
check_admin();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Message ID is required');
    }

    $message_id = intval($_GET['id']);
    
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Prepare and execute delete query
    $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Message not found');
        }
    } else {
        throw new Exception('Failed to delete message');
    }

    mysqli_close($db);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 
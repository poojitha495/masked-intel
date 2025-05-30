<?php
require_once 'config.php';

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Check table structure
    echo "Table Structure:\n";
    $structure_query = "DESCRIBE users";
    $result = mysqli_query($db, $structure_query);
    while ($row = mysqli_fetch_assoc($result)) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
    }

    echo "\nTable Data:\n";
    $data_query = "SELECT id, name, email, role, status FROM users";
    $result = mysqli_query($db, $data_query);
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Role: {$row['role']}, Status: {$row['status']}\n";
    }

    mysqli_close($db);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
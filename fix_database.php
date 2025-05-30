<?php
require_once 'config.php';

try {
    // Connect to database
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Check if organization column exists
    $result = mysqli_query($db, "SHOW COLUMNS FROM messages LIKE 'organization'");
    if (mysqli_num_rows($result) == 0) {
        // Add organization column if it doesn't exist
        $alter_query = "ALTER TABLE messages ADD COLUMN organization VARCHAR(255) AFTER phone";
        if (!mysqli_query($db, $alter_query)) {
            throw new Exception("Failed to add organization column: " . mysqli_error($db));
        }
        echo "Successfully added organization column to messages table.\n";
    } else {
        echo "Organization column already exists in messages table.\n";
    }

    mysqli_close($db);
    echo "Database check completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
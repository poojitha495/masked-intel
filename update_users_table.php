<?php
require_once 'config.php';

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Check if name column exists
    $check_column = "SHOW COLUMNS FROM users LIKE 'name'";
    $result = mysqli_query($db, $check_column);
    
    if (mysqli_num_rows($result) == 0) {
        // Add name column if it doesn't exist
        $alter_query = "ALTER TABLE users ADD COLUMN name VARCHAR(50) NOT NULL AFTER id";
        if (!mysqli_query($db, $alter_query)) {
            throw new Exception("Failed to add name column: " . mysqli_error($db));
        }
        echo "Successfully added name column to users table.\n";
    } else {
        echo "name column already exists.\n";
    }

    mysqli_close($db);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
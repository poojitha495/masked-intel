<?php
require_once 'config.php';

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Add last_login column if it doesn't exist
    $check_column = "SHOW COLUMNS FROM admins LIKE 'last_login'";
    $result = mysqli_query($db, $check_column);
    
    if (mysqli_num_rows($result) == 0) {
        $alter_query = "ALTER TABLE admins ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL";
        if (!mysqli_query($db, $alter_query)) {
            throw new Exception("Failed to add last_login column: " . mysqli_error($db));
        }
        echo "Successfully added last_login column to admins table.\n";
    } else {
        echo "last_login column already exists.\n";
    }

    mysqli_close($db);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
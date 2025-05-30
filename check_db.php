<?php
require_once 'config.php';

try {
    // Connect to database
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    echo "Database connection successful\n";

    // Check if database exists
    $result = mysqli_query($db, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    if (mysqli_num_rows($result) == 0) {
        echo "Database '" . DB_NAME . "' does not exist!\n";
    } else {
        echo "Database '" . DB_NAME . "' exists\n";
    }

    // Check if messages table exists
    $result = mysqli_query($db, "SHOW TABLES LIKE 'messages'");
    if (mysqli_num_rows($result) == 0) {
        echo "Table 'messages' does not exist!\n";
    } else {
        echo "Table 'messages' exists\n";
        
        // Show table structure
        $result = mysqli_query($db, "SHOW CREATE TABLE messages");
        if ($row = mysqli_fetch_array($result)) {
            echo "\nTable Structure:\n" . $row[1] . "\n";
        }
        
        // Show columns
        $result = mysqli_query($db, "SHOW COLUMNS FROM messages");
        echo "\nColumns:\n";
        while ($row = mysqli_fetch_assoc($result)) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    }

    mysqli_close($db);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
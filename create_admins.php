<?php
require_once 'config.php';

try {
    // Connect to database
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Create admins table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!mysqli_query($db, $create_table)) {
        throw new Exception("Failed to create admins table: " . mysqli_error($db));
    }

    // Define admin credentials
    $admins = [
        ['email' => 'n210888@rguktn.ac.in', 'password' => 'susi@123'],
        ['email' => 'n210988@rguktn.ac.in', 'password' => 'manasa#456'],
        ['email' => 'n210942@rguktn.ac.in', 'password' => 'likki^789'],
        ['email' => 'n210495@rguktn.ac.in', 'password' => 'pooja$012'],
        ['email' => 'n210494@rguktn.ac.in', 'password' => 'lasya%345']
    ];

    // Clear existing admins
    mysqli_query($db, "TRUNCATE TABLE admins");

    // Insert admin credentials
    $stmt = $db->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
    
    foreach ($admins as $admin) {
        // Hash the password for security
        $hashed_password = password_hash($admin['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $admin['email'], $hashed_password);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert admin: " . $stmt->error);
        }
    }

    echo "Admin accounts created successfully!\n";
    mysqli_close($db);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
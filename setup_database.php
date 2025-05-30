<?php
require_once 'config.php';

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Disable foreign key checks
    mysqli_query($db, "SET FOREIGN_KEY_CHECKS = 0");

    // Drop existing users table if it exists
    $drop_table = "DROP TABLE IF EXISTS users";
    if (!mysqli_query($db, $drop_table)) {
        throw new Exception("Failed to drop existing users table: " . mysqli_error($db));
    }

    // Create users table
    $create_table = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('Admin', 'User') DEFAULT 'User',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!mysqli_query($db, $create_table)) {
        throw new Exception("Failed to create users table: " . mysqli_error($db));
    }

    // Add admin users
    $admin_users = [
        [
            'name' => 'Sushmitha',
            'email' => 'n210888@rguktn.ac.in',
            'password' => 'susi@123',
            'role' => 'Admin',
            'status' => 'active'
        ],
        [
            'name' => 'Manasa',
            'email' => 'n210988@rguktn.ac.in',
            'password' => 'manasa#456',
            'role' => 'Admin',
            'status' => 'active'
        ],
        [
            'name' => 'Likitha',
            'email' => 'n210942@rguktn.ac.in',
            'password' => 'likki^789',
            'role' => 'Admin',
            'status' => 'active'
        ],
        [
            'name' => 'Pooja',
            'email' => 'n210495@rguktn.ac.in',
            'password' => 'pooja$012',
            'role' => 'Admin',
            'status' => 'active'
        ],
        [
            'name' => 'Lasya',
            'email' => 'n210494@rguktn.ac.in',
            'password' => 'lasya%345',
            'role' => 'Admin',
            'status' => 'active'
        ]
    ];

    $insert_query = "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insert_query);
    
    foreach ($admin_users as $user) {
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("sssss", 
            $user['name'],
            $user['email'],
            $hashed_password,
            $user['role'],
            $user['status']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create admin user {$user['email']}");
        }
        echo "Admin user {$user['email']} created successfully.\n";
    }

    // Re-enable foreign key checks
    mysqli_query($db, "SET FOREIGN_KEY_CHECKS = 1");

    echo "Database setup completed successfully.\n";
    
    // Verify the data
    echo "\nVerifying admin users in database:\n";
    $verify_query = "SELECT id, name, email, role, status FROM users ORDER BY id ASC";
    $result = mysqli_query($db, $verify_query);
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Role: {$row['role']}, Status: {$row['status']}\n";
    }

    mysqli_close($db);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    // Re-enable foreign key checks in case of error
    if (isset($db)) {
        mysqli_query($db, "SET FOREIGN_KEY_CHECKS = 1");
        mysqli_close($db);
    }
}
?> 
<?php
require_once 'config.php';

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Create users table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('User', 'Admin') DEFAULT 'User',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL
    )";

    if (!mysqli_query($db, $create_table)) {
        throw new Exception("Failed to create users table: " . mysqli_error($db));
    }

    // Check if table is empty, if so add some sample users
    $result = mysqli_query($db, "SELECT COUNT(*) as count FROM users");
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        // Add sample users
        $sample_users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'User',
                'status' => 'active'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => password_hash('password456', PASSWORD_DEFAULT),
                'role' => 'User',
                'status' => 'inactive'
            ]
        ];

        $insert_query = "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insert_query);

        foreach ($sample_users as $user) {
            $stmt->bind_param("sssss", 
                $user['name'],
                $user['email'],
                $user['password'],
                $user['role'],
                $user['status']
            );
            $stmt->execute();
        }
    }

    echo "Users table is ready.\n";
    mysqli_close($db);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
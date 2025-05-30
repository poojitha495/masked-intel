<?php
require_once 'config.php';

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Backup existing data
    $backup_query = "SELECT * FROM users";
    $result = mysqli_query($db, $backup_query);
    $existing_users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $existing_users[] = $row;
    }

    // Drop existing table
    $drop_table = "DROP TABLE users";
    mysqli_query($db, $drop_table);

    // Create table with correct structure
    $create_table = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('Admin', 'User') DEFAULT 'User',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL
    )";

    if (!mysqli_query($db, $create_table)) {
        throw new Exception("Failed to create users table: " . mysqli_error($db));
    }

    // Add default admin user
    $admin_user = [
        'name' => 'Admin User',
        'email' => 'admin@maskedintel.com',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'Admin',
        'status' => 'active'
    ];

    $insert_query = "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insert_query);
    
    $stmt->bind_param("sssss", 
        $admin_user['name'],
        $admin_user['email'],
        $admin_user['password'],
        $admin_user['role'],
        $admin_user['status']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create admin user");
    }

    // Restore existing users as regular users
    foreach ($existing_users as $user) {
        // Skip if email is same as admin
        if ($user['email'] === $admin_user['email']) {
            continue;
        }

        $name = $user['name'] ?? 'User ' . $user['id'];
        $role = 'User';
        $status = 'active';

        $stmt->bind_param("sssss", 
            $name,
            $user['email'],
            $user['password'],
            $role,
            $status
        );
        $stmt->execute();
    }

    echo "Users table fixed successfully.\n";
    
    // Verify the data
    echo "\nVerifying users in database:\n";
    $verify_query = "SELECT id, name, email, role, status FROM users ORDER BY role DESC, id ASC";
    $result = mysqli_query($db, $verify_query);
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Role: {$row['role']}, Status: {$row['status']}\n";
    }

    mysqli_close($db);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
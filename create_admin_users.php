<?php
require_once 'config.php';

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Check if admin exists
    $admin_check = "SELECT COUNT(*) as count FROM users WHERE role = 'Admin'";
    $result = mysqli_query($db, $admin_check);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
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
        
        if ($stmt->execute()) {
            echo "Admin user created successfully.\n";
        } else {
            throw new Exception("Failed to create admin user");
        }
    } else {
        echo "Admin user already exists.\n";
    }

    mysqli_close($db);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
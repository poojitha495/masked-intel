<?php
require_once 'config.php';

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Create messages table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        inquiry_type VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!mysqli_query($db, $create_table)) {
        throw new Exception("Failed to create messages table: " . mysqli_error($db));
    }

    // Check if table is empty, if so add some sample messages
    $result = mysqli_query($db, "SELECT COUNT(*) as count FROM messages");
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        // Add sample messages
        $sample_messages = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1 234-567-8901',
                'inquiry_type' => 'Support',
                'message' => 'I need help with accessing my account. Can you please assist?'
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.j@example.com',
                'phone' => '+1 987-654-3210',
                'inquiry_type' => 'Feedback',
                'message' => 'Great service! Just wanted to share my positive experience.'
            ]
        ];

        $insert_query = "INSERT INTO messages (name, email, phone, inquiry_type, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insert_query);

        foreach ($sample_messages as $message) {
            $stmt->bind_param("sssss", 
                $message['name'],
                $message['email'],
                $message['phone'],
                $message['inquiry_type'],
                $message['message']
            );
            $stmt->execute();
        }
    }

    echo "Messages table is ready.\n";
    mysqli_close($db);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
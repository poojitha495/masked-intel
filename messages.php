<?php
require_once 'config.php';
require_once 'check_admin.php';

// Check if user is admin
check_admin();

try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Get all messages
    $messages = [];
    $query = "SELECT * FROM messages ORDER BY created_at DESC";
    $result = mysqli_query($db, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
    }

    mysqli_close($db);
} catch (Exception $e) {
    error_log("Messages page error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while loading messages";
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Masked Intel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
            min-height: 100vh;
            color: white;
        }

        .top-nav {
            background: rgba(41, 51, 92, 0.9);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-link.active {
            background: #1a73e8;
        }

        .nav-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.1);
        }

        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: #64b5f6;
            font-size: 2rem;
            font-weight: normal;
            margin-bottom: 2rem;
        }

        .messages-table {
            width: 100%;
            background: rgba(22, 28, 51, 0.8);
            border-radius: 10px;
            border-collapse: collapse;
            overflow: hidden;
        }

        .messages-table th {
            background: rgba(26, 35, 126, 0.5);
            color: #64b5f6;
            font-weight: normal;
            text-align: left;
            padding: 1rem;
        }

        .messages-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .messages-table tr:last-child td {
            border-bottom: none;
        }

        .action-btn {
            padding: 0.3rem 0.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }

        .delete-btn {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .inquiry-type {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            background: rgba(26, 115, 232, 0.2);
            color: #1a73e8;
            display: inline-block;
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <a href="#" class="logo">
            <img src="logo.png" alt="Masked Intel Logo">
            Masked Intel
        </a>
        <div class="nav-links">
            <a href="admin.php" class="nav-link">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                Users
            </a>
            <a href="messages.php" class="nav-link active">
                <i class="fas fa-envelope"></i>
                Messages
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <div class="main-content">
        <h1>Messages</h1>
        
        <table class="messages-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Inquiry Type</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No messages found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($message['id']); ?></td>
                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                        <td><?php echo htmlspecialchars($message['phone']); ?></td>
                        <td>
                            <span class="inquiry-type">
                                <?php echo htmlspecialchars($message['inquiry_type']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($message['message']); ?></td>
                        <td>
                            <a href="#" onclick="deleteMessage(<?php echo $message['id']; ?>)" class="action-btn delete-btn">
                                Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    function deleteMessage(messageId) {
        if (confirm('Are you sure you want to delete this message?')) {
            fetch('delete_message.php?id=' + messageId, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the message');
            });
        }
    }
    </script>
</body>
</html> 
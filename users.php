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

    // Get all users (both regular users and admins)
    $users = [];
    $query = "SELECT id, name, email, role, status, last_login FROM users ORDER BY role DESC, id ASC";
    $result = mysqli_query($db, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Set status based on last_login
            if ($row['last_login']) {
                $last_login_time = strtotime($row['last_login']);
                $current_time = time();
                $difference = $current_time - $last_login_time;
                
                // If logged in within the last 15 minutes, consider them active
                $row['status'] = ($difference < 900) ? 'active' : 'inactive';
            } else {
                $row['status'] = 'inactive';
            }
            
            $users[] = $row;
        }
    }

    mysqli_close($db);
} catch (Exception $e) {
    error_log("Users page error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while loading users";
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Masked Intel</title>
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #64b5f6;
            font-size: 2rem;
            font-weight: normal;
        }

        .add-user-btn {
            background: #1a73e8;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .add-user-btn:hover {
            background: #1557b0;
            transform: translateY(-2px);
        }

        .search-box {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .search-box::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .users-table {
            width: 100%;
            background: rgba(22, 28, 51, 0.8);
            border-radius: 10px;
            border-collapse: collapse;
            overflow: hidden;
        }

        .users-table th {
            background: rgba(26, 35, 126, 0.5);
            color: #64b5f6;
            font-weight: normal;
            text-align: left;
            padding: 1rem;
        }

        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .role-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            display: inline-block;
        }

        .role-badge.admin {
            background: rgba(156, 39, 176, 0.2);
            color: #9c27b0;
        }

        .role-badge.user {
            background: rgba(26, 115, 232, 0.2);
            color: #1a73e8;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            display: inline-block;
        }

        .status-badge.active {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .status-badge.inactive {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
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

        .edit-btn {
            background: rgba(26, 115, 232, 0.2);
            color: #1a73e8;
        }

        .delete-btn {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }

        .action-btn:hover {
            transform: translateY(-2px);
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
            <a href="users.php" class="nav-link active">
                <i class="fas fa-users"></i>
                Users
            </a>
            <a href="messages.php" class="nav-link">
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
        <div class="header">
            <h1>Manage Users</h1>
            <a href="add_user.php" class="add-user-btn">
                <i class="fas fa-plus"></i>
                Add New User
            </a>
        </div>

        <input type="text" class="search-box" placeholder="Search users..." onkeyup="searchUsers(this.value)">

        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No users found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="role-badge <?php echo strtolower($user['role']); ?>">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $user['status']; ?>">
                                <?php 
                                if ($user['status'] === 'active') {
                                    echo 'Online';
                                } else {
                                    echo $user['last_login'] 
                                        ? 'Last seen: ' . date('M d, H:i', strtotime($user['last_login']))
                                        : 'Never logged in';
                                }
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['role'] !== 'Admin' || $_SESSION['email'] === $user['email']): ?>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn">
                                Edit
                            </a>
                            <?php if ($_SESSION['email'] !== $user['email']): ?>
                            <a href="#" onclick="deleteUser(<?php echo $user['id']; ?>)" class="action-btn delete-btn">
                                Delete
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    function searchUsers(query) {
        const rows = document.querySelectorAll('.users-table tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query.toLowerCase()) ? '' : 'none';
        });
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch('delete_user.php?id=' + userId, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the user');
            });
        }
    }
    </script>
</body>
</html> 
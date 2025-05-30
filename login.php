<?php
require_once 'config.php';

$error = '';
// Remove debug variable since we won't be displaying it
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$db) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }

        // Check user in the users table
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Update last login time
                $update_stmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("i", $user['id']);
                    $update_stmt->execute();
                }
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['is_admin'] = ($user['role'] === 'Admin');
                $_SESSION['current_login'] = date('F d, Y \a\t H:i');
                $_SESSION['last_login'] = $user['last_login'] ? date('F d, Y \a\t H:i', strtotime($user['last_login'])) : 'First Login';
                
                // Redirect based on role
                if ($user['role'] === 'Admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            }
        }
        
        // If we get here, either user not found or password wrong
        $error = "Invalid email or password";

    } catch (Exception $e) {
        $error = "An error occurred. Please try again later.";
        error_log($e->getMessage()); // Log error but don't show to user
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Masked Intel</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 3rem auto;
            padding: 2rem;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.1) 100%);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.2);
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .error-message {
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
            background: rgba(255, 107, 107, 0.1);
        }

        h1 {
            text-align: center;
            color: #fff;
            margin-bottom: 2rem;
        }

        label {
            color: #fff;
            font-weight: 600;
        }

        input {
            padding: 0.8rem 1rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #4a90e2;
        }

        button {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .register-link {
            text-align: center;
            margin-top: 1rem;
            color: #fff;
        }

        .register-link a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="geometric-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register first</a>
        </div>
    </div>
</body>
</html>
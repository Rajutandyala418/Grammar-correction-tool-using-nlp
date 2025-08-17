<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '/include/db_connect.php'); // DB connection

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare SQL
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, password FROM users WHERE username = ?");
    
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $first_name, $last_name, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // ✅ Use same session variables as dashboard.php
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Invalid username or password.";
            }
        } else {
            $message = "Invalid username or password.";
        }
        $stmt->close();
    } else {
        $message = "Database query error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap');

        body {
            margin: 0; padding: 0;
            font-family: 'Poppins', sans-serif;
            display: flex; justify-content: center; align-items: center;
            height: 100vh;
            color: white;
            background: linear-gradient(
                135deg,
                #ff0000, #ff7f00, #ffff00, #7fff00, #00ff00,
                #00ff7f, #00ffff, #007fff, #0000ff, #7f00ff,
                #ff00ff, #ff007f, #ff6666, #ff9966, #ffcc66,
                #ccff66, #66ff66, #66ffcc, #66ccff, #6699ff,
                #6666ff, #9966ff, #cc66ff, #ff66ff, #ff66cc
            );
            background-size: 400% 400%;
            animation: gradientAnimation 20s ease infinite;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-box {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            width: 500px; /* ✅ Increased width */
            z-index: 1;
        }
        h2 { margin-bottom: 20px; color: #fff; }
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        label {
            flex: 1;
            text-align: right;
            margin-right: 10px;
            font-weight: bold;
        }
        input {
            flex: 2;
            padding: 10px;
            border-radius: 5px;
            border: none;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            color: #333;
        }
        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 5px;
            border: none;
            font-size: 1rem;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            color: white; cursor: pointer;
        }
        button:hover {
            background: linear-gradient(90deg, #dd2476, #ff512f);
        }
        .message { color: #ff8080; font-size: 0.9rem; margin-bottom: 10px; }
        a {
            display: block;
            margin-top: 10px;
            text-decoration: none;
            color: #fff;
            font-size: 0.9rem;
        }
        .back-to-main {
            position: absolute;
            top: 20px;
            right: 30px;
            padding: 10px 20px;
            background: linear-gradient(90deg, #ff512f, #dd2476);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        .back-to-main:hover {
            opacity: 0.85;
        }
    </style>
</head>
<body>

<a href="index.php" class="back-to-main">Back to Main Page</a>

<div class="login-box">
    <h2>Sign in</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" placeholder="Enter Username" autocomplete="off" required>
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter Password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <a href="forgot_password.php">Forgot Password?</a>
</div>

</body>
</html>

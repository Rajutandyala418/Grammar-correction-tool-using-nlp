<?php
include(__DIR__ . '/include/db_connect.php');

$message = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'username') {
        $phone = trim($_POST['phone']);
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE phone = ?");
        if ($stmt) {
            $stmt->bind_param("s", $phone);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($username, $email);
                $stmt->fetch();
                $message = "✅ Account Found:<br><strong>Username:</strong> " . htmlspecialchars($username) . 
                           "<br><strong>Email:</strong> " . htmlspecialchars($email);
            } else {
                $message = "❌ No account found for this phone number.";
            }
            $stmt->close();
        } else {
            $message = "❌ Query error: " . $conn->error;
        }

    } elseif ($action === 'password') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        // ✅ Fix: use user_id instead of id
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND email = ? AND phone = ?");
        if ($stmt) {
            $stmt->bind_param("sss", $username, $email, $phone);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // ✅ Redirect to update_password.php with username
                header("Location: update_password.php?username=" . urlencode($username));
                exit();
            } else {
                $message = "❌ No account matches the provided details.";
            }
            $stmt->close();
        } else {
            $message = "❌ Query error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Username/Password</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
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
        min-height: 100vh;
        overflow-y: auto;
    }
    @keyframes gradientAnimation {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .forgot-box {
        background: rgba(0,0,0,0.5);
        border-radius: 12px;
        width: 500px;
        margin: 100px auto;
        padding: 30px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        position: relative;
    }

    h2 { margin-bottom: 20px; }

    select, input, button {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border-radius: 6px;
        border: none;
        font-size: 1rem;
    }
    select, input { background: #fff; color: #333; }
    button {
        background: linear-gradient(90deg, #ff512f, #dd2476);
        color: white;
        cursor: pointer;
        transition: 0.3s;
    }
    button:hover { background: linear-gradient(90deg, #dd2476, #ff512f); }

    .message { 
        font-size: 0.95rem; 
        margin-top: 10px; 
        color: white; 
    }

    .back-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 20px;
        background: linear-gradient(90deg, #ff512f, #dd2476);
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
    }
    .back-btn:hover { opacity: 0.85; }

    .form-section { display: none; }
    .active { display: block; }
</style>
</head>
<body>

<a href="login.php" class="back-btn">Back to Login</a>

<div class="forgot-box">
    <h2>Forgot Username / Password</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <select id="forgotType">
        <option value="username" selected>Forgot Username</option>
        <option value="password">Forgot Password</option>
    </select>

    <!-- Forgot Username Form -->
    <form method="post" id="formUsername" class="form-section active">
        <input type="hidden" name="action" value="username">
        <input type="text" name="phone" placeholder="Enter Phone Number" required>
        <button type="submit">Find Username</button>
    </form>

    <!-- Forgot Password Form -->
    <form method="post" id="formPassword" class="form-section">
        <input type="hidden" name="action" value="password">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="text" name="phone" placeholder="Enter Phone Number" required>
        <button type="submit">Reset Password</button>
    </form>
</div>

<script>
    const selectBox = document.getElementById('forgotType');
    const formUsername = document.getElementById('formUsername');
    const formPassword = document.getElementById('formPassword');

    selectBox.addEventListener('change', function() {
        if (this.value === 'username') {
            formUsername.classList.add('active');
            formPassword.classList.remove('active');
        } else {
            formPassword.classList.add('active');
            formUsername.classList.remove('active');
        }
    });
</script>

</body>
</html>

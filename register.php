<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include(__DIR__ . '/include/db_connect.php');

$show_popup = false;
$popup_message = "";
$redirect_time = 5; // seconds to redirect

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $username   = trim($_POST['username']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // ✅ Check for duplicates
    $check_sql = "SELECT * FROM users WHERE email=? OR phone=? OR username=?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("sss", $email, $phone, $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $popup_message = "❌ Error: Email, phone, or username already exists!";
        $show_popup = true;
    } else {
        // ✅ Insert new user
        $sql = "INSERT INTO users (first_name, last_name, email, phone, username, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone, $username, $password);

            if ($stmt->execute()) {
                $popup_message = "🎉 Congratulations, you are successfully registered!<br>Redirecting to login page in <span id='countdown'>$redirect_time</span> seconds...";
                $show_popup = true;
            } else {
                $popup_message = "❌ Error: " . $stmt->error;
                $show_popup = true;
            }
            $stmt->close();
        } else {
            $popup_message = "❌ Prepare failed: " . $conn->error;
            $show_popup = true;
        }
    }
    $check_stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap');
body {
    font-family: 'Poppins', sans-serif;
    margin: 0; color: white;
    background: linear-gradient(135deg,#ff0000,#ff7f00,#ffff00,#7fff00,#00ff00,
        #00ff7f,#00ffff,#007fff,#0000ff,#7f00ff,#ff00ff,#ff007f,#ff6666,#ff9966,
        #ffcc66,#ccff66,#66ff66,#66ffcc,#66ccff,#6699ff,#6666ff,#9966ff,#cc66ff,#ff66ff,#ff66cc);
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
.register-box {
    background: rgba(0,0,0,0.5);
    border-radius: 12px;
    width: 650px;
    margin: 80px auto;
    padding: 40px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.5);
}
h2 { text-align: center; margin-bottom: 20px; }
.form-group { display: flex; align-items: center; margin: 12px 0; }
label { width: 180px; font-weight: 600; text-align: right; margin-right: 15px; }
input { flex: 1; padding: 12px; border-radius: 6px; border: none; font-size: 1rem; background: #fff; color: #333; }
button {
    width: 100%; padding: 14px; margin-top: 20px; border-radius: 6px;
    border: none; font-size: 1rem; background: linear-gradient(90deg, #ff512f, #dd2476); color: white; cursor: pointer; transition: 0.3s;
}
button:hover { background: linear-gradient(90deg, #dd2476, #ff512f); }

/* ✅ Popup */
.popup { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; background: rgba(0,0,0,0.6); z-index: 9999; }
.popup-content { background: #fff; padding: 30px; border-radius: 12px; text-align: center; max-width: 400px; width: 90%; box-shadow: 0px 4px 10px rgba(0,0,0,0.3); color: #000; font-family: 'Poppins', sans-serif; }
.popup-content h2 { margin-bottom: 10px; }
#error-message { color: yellow; font-weight: 600; margin-top: 10px; text-align: center; }
</style>
</head>
<body>
<a href="index.php" style="position: absolute; top: 15px; right: 20px; background: #007bff; color: #fff; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold;">Back to Main Page</a>

<div class="register-box">
<h2>Create Account</h2>
<form method="POST" onsubmit="return validateForm()">
    <div class="form-group">
        <label>First Name:</label>
        <input type="text" name="first_name" placeholder="Enter your first name" required>
    </div>
    <div class="form-group">
        <label>Last Name:</label>
        <input type="text" name="last_name" placeholder="Enter your last name" required>
    </div>
    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" placeholder="Enter your email address" required>
    </div>
    <div class="form-group">
        <label>Phone Number:</label>
        <input type="text" name="phone" placeholder="Enter your phone number" required>
    </div>
    <div class="form-group">
        <label>Username:</label>
        <input type="text" name="username" placeholder="Choose a username" required>
    </div>
    <div class="form-group">
        <label>Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
    </div>
    <div class="form-group">
        <label>Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
    </div>
    <div id="error-message"></div>
    <button type="submit">Register</button>
</form>
</div>

<?php if ($show_popup): ?>
<div class="popup">
    <div class="popup-content">
        <h2><?php echo (strpos($popup_message, "❌") !== false) ? "Error" : "Success"; ?></h2>
        <p><?php echo $popup_message; ?></p>
    </div>
</div>
<?php if (strpos($popup_message, "successfully") !== false): ?>
<script>
let seconds = <?php echo $redirect_time; ?>;
let countdownElem = document.getElementById("countdown");
let interval = setInterval(() => {
    seconds--;
    countdownElem.textContent = seconds;
    if (seconds <= 0) {
        clearInterval(interval);
        window.location.href = "login.php";
    }
}, 1000);
</script>
<?php endif; ?>
<?php endif; ?>

<script>
function validateForm() {
    const firstName = document.querySelector('input[name="first_name"]').value.trim();
    const lastName = document.querySelector('input[name="last_name"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    const username = document.querySelector('input[name="username"]').value.trim();
    const pwd = document.getElementById("password").value;
    const cpwd = document.getElementById("confirm_password").value;
    const errorBox = document.getElementById("error-message");

    // First and last name
    if(firstName.length < 4) { errorBox.textContent = "❌ First name must be at least 4 characters."; return false; }
    if(lastName.length < 4) { errorBox.textContent = "❌ Last name must be at least 4 characters."; return false; }

    // Email regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailRegex.test(email)) { errorBox.textContent = "❌ Enter a valid email (name@domain.com)."; return false; }

    // Username regex: at least 10 chars, letters/numbers/underscore, no spaces
    const usernameRegex = /^[A-Za-z0-9_]{10,}$/;
    if(!usernameRegex.test(username)) { errorBox.textContent = "❌ Username must be at least 10 characters and contain only letters, numbers, or '_', no spaces."; return false; }

    // Password validation
    const regexUpper = /[A-Z]/;
    const regexLower = /[a-z]/;
    const regexDigit = /[0-9]/;
    const regexSpecial = /[!@#$%^&*(),.?":{}|<>]/;

    if(pwd.length < 8) { errorBox.textContent = "❌ Password must be at least 8 characters."; return false; }
    if(!regexUpper.test(pwd)) { errorBox.textContent = "❌ Password must contain at least one uppercase letter."; return false; }
    if(!regexLower.test(pwd)) { errorBox.textContent = "❌ Password must contain at least one lowercase letter."; return false; }
    if(!regexDigit.test(pwd)) { errorBox.textContent = "❌ Password must contain at least one digit."; return false; }
    if(!regexSpecial.test(pwd)) { errorBox.textContent = "❌ Password must contain at least one special character."; return false; }
    if(pwd !== cpwd) { errorBox.textContent = "❌ Password and Confirm Password do not match."; return false; }

    errorBox.textContent = ""; // clear
    return true;
}
</script>
</body>
</html>

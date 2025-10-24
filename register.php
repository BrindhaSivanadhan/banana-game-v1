<?php
ob_start(); // start output buffering
include 'config.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validate
    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters long.";
    } else {
        // Check if username exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $res = $check->get_result();

        if ($res && $res->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            // Insert new user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);

            if ($stmt->execute()) {
                //  Auto-login immediately after registration
                $_SESSION['username'] = $username;
                header("Location: select_difficulty.php");
                exit();
            } else {
                $error = "Database error. Try again.";
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Register — Banana Game</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {font-family:Poppins,sans-serif;background:#f9fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}
    .register-box {background:white;border-radius:20px;padding:40px;width:360px;box-shadow:0 8px 24px rgba(0,0,0,0.08);text-align:center;}
    h2 {margin-bottom:20px;color:#333;}
    input {width:100%;padding:10px;margin:8px 0;border-radius:8px;border:1px solid #ccc;}
    button {background:#bde0fe;color:#333;padding:10px 18px;border:none;border-radius:10px;font-weight:600;cursor:pointer;}
    button:hover {background:#a6d2fc;}
    .error {color:#ff4d4d;font-weight:600;}
    a {color:#444;text-decoration:none;font-size:14px;}
  </style>
</head>
<body>
  <div class="register-box">
    <h2>Create Account</h2>

    <?php if($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="username" placeholder="Enter username" required>
      <input type="password" name="password" placeholder="Enter password" required>
      <input type="password" name="confirm_password" placeholder="Confirm password" required>
      <button type="submit" name="register">Register</button>
    </form>

    <p style="margin-top:10px;">Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
<?php ob_end_flush(); ?>

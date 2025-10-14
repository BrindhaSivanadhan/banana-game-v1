<?php
ob_start();
session_start();
include 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters long.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $res = $check->get_result();

        if ($res && $res->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);

            if ($stmt->execute()) {
                // Auto-login and redirect to select_difficulty (or index)
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
<head><meta charset="utf-8"/><title>Register</title></head>
<body>
  <h2>Create Account</h2>
  <?php if($error): ?><p style="color:red;"><?=htmlspecialchars($error)?></p><?php endif; ?>
  <form method="POST">
    <input name="username" required placeholder="Username"><br>
    <input name="password" type="password" required placeholder="Password"><br>
    <input name="confirm_password" type="password" required placeholder="Confirm"><br>
    <button name="register" type="submit">Register</button>
  </form>
  <p>Already have account? <a href="login.php">Login</a></p>
</body>
</html>

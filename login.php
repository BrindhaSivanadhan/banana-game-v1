<?php
ob_start();
session_start();
include 'config.php';

$err = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $username;
                header("Location: select_difficulty.php");
                exit();
            } else {
                $err = "Invalid password.";
            }
        } else {
            $err = "User not found.";
        }
    } else {
        $err = "DB error.";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"/><title>Login</title></head>
<body>
  <h2>Login</h2>
  <?php if($err): ?><p style="color:red;"><?=htmlspecialchars($err)?></p><?php endif; ?>
  <form method="POST">
    <input name="username" required placeholder="Username"><br>
    <input name="password" type="password" required placeholder="Password"><br>
    <button name="login" type="submit">Login</button>
  </form>
  <p>No account? <a href="register.php">Register</a></p>
</body>
</html>

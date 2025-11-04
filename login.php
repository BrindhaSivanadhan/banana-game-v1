<?php
ob_start();
include 'config.php';
session_start();

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

                // PHP redirect (preferred)
                header("Location: select_difficulty.php");
                exit();

                // If header fails, JS fallback (kept for safety)
                // echo "<script>window.location.href='select_difficulty.php';</script>";
                // exit();
            } else {
                $err = "Invalid password.";
            }
        } else {
            $err = "User not found.";
        }
    } else {
        $err = "Database error.";
    }
}
$registered = isset($_GET['registered']) ? true : false;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Login — Banana Game</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-bg">
  <div class="login-modal">
    <img src="banana_logo.png" alt="Banana Logo" class="banana-logo">
    <h3>Login</h3>

    <?php if($registered): ?><p class="success">Registration successful. Please login.</p><?php endif; ?>
    <?php if($err): ?><p class="error"><?=htmlspecialchars($err)?></p><?php endif; ?>

    <form method="POST" class="login-form">
      <label>Username: <input name="username" required></label>
      <label>Password: <input name="password" type="password" required></label>
      <div class="login-actions">
        <button name="login" type="submit" class="btn primary">Login</button>
        <a href="register.php" class="btn secondary">Register</a>
      </div>
    </form>
  </div>
</body>
</html>
<?php ob_end_flush(); ?> 

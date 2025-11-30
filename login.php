<?php
include 'config.php';
include 'session.php';

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
                login_user($username);

                // If the player collected points as a guest, move them to the account now
                if (!empty($_SESSION['guest_pending_points']) && is_array($_SESSION['guest_pending_points'])) {
                    $allowedLevels = ['beginner', 'intermediate', 'advanced'];
                    $recovered = [];

                    foreach ($_SESSION['guest_pending_points'] as $lvl => $pts) {
                        $points = (int)$pts;
                        if ($points <= 0 || !in_array($lvl, $allowedLevels, true)) {
                            continue;
                        }
                        $column = $lvl . '_points';
                        $updateSql = "UPDATE users SET $column = $column + ? WHERE username = ?";
                        $updateStmt = $conn->prepare($updateSql);
                        if ($updateStmt) {
                            $updateStmt->bind_param("is", $points, $username);
                            $updateStmt->execute();
                            if ($updateStmt->affected_rows > 0) {
                                $recovered[] = ucfirst($lvl) . " +" . $points;
                            }
                            $updateStmt->close();
                        }
                    }

                    if (!empty($recovered)) {
                        $_SESSION['flash_message'] = "Guest progress recovered: " . implode(', ', $recovered);
                        $_SESSION['flash_message_type'] = 'success';
                    }

                    unset($_SESSION['guest_pending_points']);
                }

                header("Location: select_difficulty.php");
                exit();
            } else { $err = "Invalid password."; }
        } else { $err = "User not found."; }
    } else { $err = "DB error."; }
}

$registered = isset($_GET['registered']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Login — Banana Bliss</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    
    :root {
      --banana-yellow: #FFE135;
      --banana-dark: #FFB800;
      --banana-light: #FFF4A3;
      --bg-dark: #3a2f1a;
      --bg-darker: #2d2415;
      --text-light: #fff9e6;
      --text-muted: #d4c5a0;
    }

    html, body { 
      height: 100%; 
      margin: 0;
      overflow: hidden;
    }
    
    body {
      font-family: "Fredoka", sans-serif;
      background: linear-gradient(135deg, #2d2415 0%, #3a2f1a 50%, #4a3d20 100%);
      color: var(--text-light);
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      padding: 20px;
    }

    /* Floating banana background */
    .bg-bananas {
      position: fixed;
      inset: 0;
      pointer-events: none;
      overflow: hidden;
      z-index: 0;
    }
    
    .floating-banana {
      position: absolute;
      font-size: 40px;
      opacity: 0.15;
      animation: float 20s infinite ease-in-out;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      25% { transform: translateY(-30px) rotate(5deg); }
      50% { transform: translateY(-60px) rotate(-5deg); }
      75% { transform: translateY(-30px) rotate(3deg); }
    }

    /* Login box */
    .login-container {
      position: relative;
      z-index: 1;
      width: 95%;
      max-width: 420px;
      animation: slideIn 0.6s ease-out;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-box {
      background: rgba(255, 244, 163, 0.1);
      backdrop-filter: blur(15px);
      border-radius: 24px;
      padding: 35px 32px;
      border: 1px solid rgba(255, 225, 53, 0.2);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
      text-align: center;
    }

    /* Logo and title */
    .logo-section {
      margin-bottom: 24px;
    }

    .banana-icon {
      font-size: 48px;
      animation: bounce 2s infinite;
      display: inline-block;
      margin-bottom: 8px;
    }
    
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    h2 {
      margin: 0 0 6px 0;
      font-size: 28px;
      font-weight: 700;
      background: linear-gradient(135deg, var(--banana-yellow) 0%, var(--banana-dark) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .subtitle {
      color: var(--text-muted);
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    /* Success message */
    .success {
      background: rgba(129, 236, 114, 0.15);
      border: 1px solid rgba(95, 211, 93, 0.4);
      color: #81ec72;
      padding: 10px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 13px;
      margin-bottom: 16px;
    }

    /* Error message */
    .error {
      background: rgba(255, 82, 82, 0.15);
      border: 1px solid rgba(255, 82, 82, 0.4);
      color: #ff8a8a;
      padding: 10px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 13px;
      margin-bottom: 16px;
      animation: shake 0.5s;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-8px); }
      50% { transform: translateX(8px); }
      75% { transform: translateX(-8px); }
    }

    /* Form styling */
    form {
      margin-bottom: 18px;
    }

    .input-group {
      margin-bottom: 14px;
      text-align: left;
    }

    label {
      display: block;
      color: var(--text-muted);
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 5px;
      margin-left: 4px;
    }

    input {
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 2px solid rgba(255, 225, 53, 0.2);
      background: rgba(255, 255, 255, 0.05);
      color: var(--text-light);
      font-size: 14px;
      font-weight: 600;
      font-family: "Fredoka", sans-serif;
      transition: all 0.3s ease;
    }
    
    input::placeholder {
      color: rgba(212, 197, 160, 0.5);
    }
    
    input:focus {
      outline: none;
      border-color: var(--banana-yellow);
      background: rgba(255, 225, 53, 0.08);
      box-shadow: 0 0 0 4px rgba(255, 225, 53, 0.1);
    }

    /* Buttons */
    .btn {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 700;
      font-family: "Fredoka", sans-serif;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 8px;
      text-decoration: none;
      display: block;
      text-align: center;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--banana-yellow) 0%, var(--banana-dark) 100%);
      color: #3a2f1a;
      box-shadow: 0 10px 30px rgba(255, 225, 53, 0.3);
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(255, 225, 53, 0.4);
    }

    .btn-secondary {
      background: rgba(255, 225, 53, 0.15);
      color: var(--banana-yellow);
      border: 2px solid rgba(255, 225, 53, 0.3);
    }
    
    .btn-secondary:hover {
      background: rgba(255, 225, 53, 0.25);
      transform: translateY(-2px);
    }

    .btn-guest {
      background: linear-gradient(135deg, #FFD54F 0%, #FFA726 100%);
      color: #3a2f1a;
      box-shadow: 0 8px 24px rgba(255, 167, 38, 0.3);
    }
    
    .btn-guest:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(255, 167, 38, 0.4);
    }
    
    .btn:active {
      transform: translateY(-1px);
    }

    /* Footer text */
    .footer-text {
      color: var(--text-muted);
      font-size: 13px;
      font-weight: 600;
      margin-top: 18px;
    }
    
    .footer-text a {
      color: var(--banana-yellow);
      text-decoration: none;
      font-weight: 700;
      transition: all 0.2s ease;
    }
    
    .footer-text a:hover {
      color: var(--banana-light);
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 480px) {
      .login-box {
        padding: 35px 25px;
        border-radius: 24px;
      }
      
      .banana-icon {
        font-size: 50px;
      }
      
      h2 {
        font-size: 28px;
      }
      
      input {
        padding: 12px 14px;
        font-size: 14px;
      }
      
      .btn {
        padding: 14px;
        font-size: 16px;
      }
    }
  </style>
</head>
<body>
  <!-- Floating banana background -->
  <div class="bg-bananas">
    <div class="floating-banana" style="top: 10%; left: 5%;">🍌</div>
    <div class="floating-banana" style="top: 20%; right: 8%; animation-delay: -5s;">🍌</div>
    <div class="floating-banana" style="top: 60%; left: 10%; animation-delay: -10s;">🍌</div>
    <div class="floating-banana" style="top: 70%; right: 15%; animation-delay: -15s;">🍌</div>
    <div class="floating-banana" style="top: 40%; left: 85%; animation-delay: -7s;">🍌</div>
    <div class="floating-banana" style="top: 85%; left: 50%; animation-delay: -12s;">🍌</div>
  </div>

  <div class="login-container">
    <div class="login-box">
      <!-- Logo section -->
      <div class="logo-section">
        <div class="banana-icon">🍌</div>
        <h2>Banana Bliss</h2>
        <p class="subtitle">Welcome back! Please login to continue</p>
      </div>

      <!-- Success message -->
      <?php if($registered): ?>
        <div class="success">✅ Registration successful! Please login.</div>
      <?php endif; ?>

      <!-- Error message -->
      <?php if($err): ?>
        <div class="error">⚠️ <?= htmlspecialchars($err) ?></div>
      <?php endif; ?>

      <!-- Login form -->
      <form method="POST">
        <div class="input-group">
          <label for="username">Username</label>
          <input 
            type="text" 
            id="username"
            name="username" 
            placeholder="Enter your username" 
            required
            autocomplete="username"
          >
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input 
            type="password" 
            id="password"
            name="password" 
            placeholder="Enter your password" 
            required
            autocomplete="current-password"
          >
        </div>

        <button type="submit" name="login" class="btn btn-primary">🎮 Login</button>
        <a href="register.php" class="btn btn-secondary">Create Account</a>
        <a href="puzzle_match.php?level=beginner&guest=1" class="btn btn-guest">🍌 Continue as Guest</a>
      </form>
    </div>
  </div>
</body>
</html>
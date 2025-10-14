<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

// get user points
$username = $_SESSION['username'];
$sql = "SELECT beginner_points, intermediate_points, advanced_points FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Select Difficulty</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { 
      background: #f7fafc; 
      font-family: "Poppins", sans-serif; 
      margin:0; 
      padding:0; 
      display:flex; 
      justify-content:center; 
      align-items:center; 
      min-height:100vh; 
    }
    .container { 
      position: relative; 
      width: 760px; 
      background: #fff; 
      border-radius: 16px; 
      padding: 36px 42px; 
      box-shadow: 0 10px 30px rgba(20,30,50,0.06); 
      text-align:center; 
    }
    .top-right { position: absolute; right: 20px; top: 20px; }
    .points-button { 
      background:#cde3ff; 
      color:#05386b; 
      padding:8px 14px; 
      border-radius:10px; 
      text-decoration:none; 
      font-weight:600; 
      cursor:pointer; 
      border:none; 
    }
    .points-popup { 
      position:absolute; 
      right:20px; 
      top:64px; 
      width:220px; 
      background:#fff; 
      border-radius:10px; 
      box-shadow:0 8px 20px rgba(0,0,0,0.08); 
      padding:12px; 
      display:none; 
      z-index:20; 
    }
    .points-popup h4{ 
      margin:0 0 8px 0; 
      font-size:14px; 
      color:#222; 
    }
    .points-row { 
      display:flex; 
      justify-content:space-between; 
      padding:6px 0; 
      font-weight:600; 
      color:#444; 
    }
    .difficulty-wrap { 
      display:flex; 
      flex-direction:column; 
      gap:18px; 
      align-items:center; 
      margin-top:12px; 
    }
    .big-btn { 
      display:block; 
      width: 100%; 
      max-width: 480px; 
      padding:18px 24px; 
      border-radius:14px; 
      font-size:20px; 
      font-weight:700; 
      text-decoration:none; 
      color:#fff; 
      box-shadow:0 8px 20px rgba(10,20,40,0.04); 
      transition:transform .15s ease; 
    }
    .big-btn:hover { transform:translateY(-4px); }
    .btn-beginner { background: linear-gradient(90deg,#d1f7c4,#b8f0a2); color:#2b5a2b; }
    .btn-intermediate { background: linear-gradient(90deg,#fff6c8,#ffe7a4); color:#6b5900; }
    .btn-advanced { background: linear-gradient(90deg,#ffd9d9,#ffb7b7); color:#7a2b2b; }
  </style>
</head>
<body>
  <div class="container">
    <div class="top-right">
      <button id="pointsBtn" class="points-button">View Points</button>
      <div id="pointsPopup" class="points-popup" aria-hidden="true">
        <h4>Your Points</h4>
        <div class="points-row"><span>Beginner</span><span><?=htmlspecialchars($user['beginner_points'] ?? 0)?></span></div>
        <div class="points-row"><span>Intermediate</span><span><?=htmlspecialchars($user['intermediate_points'] ?? 0)?></span></div>
        <div class="points-row"><span>Advanced</span><span><?=htmlspecialchars($user['advanced_points'] ?? 0)?></span></div>
        <div style="text-align:right;margin-top:8px;">
          <a href="index.php" style="text-decoration:none;color:#5c6b7a;font-weight:600;">Profile</a>
        </div>
      </div>
    </div>

    <h2>Select Difficulty Level</h2>

    <div class="difficulty-wrap">
      <!-- Beginner button now goes to puzzle_match.php -->
      <a class="big-btn btn-beginner" href="puzzle_match.php?level=beginner">Beginner</a>

      <!-- Intermediate button stays on banana.php -->
      <a class="big-btn btn-intermediate" href="banana.php">Intermediate</a>

      <!-- Advanced button can stay pointing to future puzzle or new game -->
      <a class="big-btn btn-advanced" href="puzzle.php?level=5">Advanced</a>
    </div>
  </div>

  <script>
    const btn = document.getElementById('pointsBtn');
    const popup = document.getElementById('pointsPopup');
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const visible = popup.style.display === 'block';
      popup.style.display = visible ? 'none' : 'block';
      popup.setAttribute('aria-hidden', visible ? 'true' : 'false');
    });
    document.addEventListener('click', (e) => {
      if(!popup.contains(e.target) && e.target !== btn){
        popup.style.display = 'none';
        popup.setAttribute('aria-hidden','true');
      }
    });
  </script>
</body>
</html>


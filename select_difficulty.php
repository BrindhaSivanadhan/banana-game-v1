<?php
include 'config.php';
include 'session.php';
require_login(); // redirects to login.php if not logged in

$username = $_SESSION['username'];

// Fetch user points
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
  <title>Select Difficulty — Banana Game</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { 
      background: #f6ede4; /* light brown shade */
      font-family: "Poppins", sans-serif;
      margin:0; padding:0;
      display:flex; justify-content:center; align-items:center;
      min-height:100vh;
    }
    .container { 
      position: relative;
      width: 760px;
      background: #fffaf5;
      border-radius: 16px;
      padding: 36px 42px;
      box-shadow: 0 10px 30px rgba(90,70,50,0.1);
      text-align:center;
    }

    /* Profile icon area */
    .profile-container {
      position: absolute;
      right: 20px;
      top: 20px;
      text-align: center;
    }
    .profile-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #ffcd94, #eab676, #ffb347);
      border-radius: 50%;
      border: 3px solid #fff;
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
      cursor: pointer;
      transition: transform 0.2s ease;
    }
    .profile-icon:hover { transform: scale(1.05); }

    .profile-name {
      font-weight: 700;
      color: #5a4632;
      font-size: 15px;
      margin-top: 6px;
    }
    .profile-level {
      font-weight: 600;
      color: #a17852;
      font-size: 13px;
    }

    /* Dropdown Menu */
    .dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 90px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      width: 200px;
      z-index: 20;
      overflow: hidden;
    }
    .dropdown a {
      display: block;
      padding: 10px 14px;
      text-decoration: none;
      color: #5a4632;
      font-weight: 600;
      border-top: 1px solid #eee;
    }
    .dropdown a:hover {
      background: #fff1df;
    }
    .points-section {
      padding: 10px 14px;
      text-align: left;
      color: #5a4632;
      background: #fff9f2;
    }
    .points-section h4 {
      font-size: 14px;
      margin: 0 0 6px;
      color: #7b5638;
    }
    .points-row {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      margin-bottom: 4px;
    }

    /* Difficulty buttons */
    .difficulty-wrap {
      display:flex;
      flex-direction:column;
      gap:18px;
      align-items:center;
      margin-top:12px;
    }
    .big-btn {
      display:block;
      width:100%;
      max-width:480px;
      padding:18px 24px;
      border-radius:14px;
      font-size:20px;
      font-weight:700;
      text-decoration:none;
      color:#fff;
      box-shadow:0 8px 20px rgba(10,20,40,0.08);
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

    <!-- Profile Section -->
    <div class="profile-container">
      <div class="profile-icon" id="profileIcon"></div>
      <div class="profile-name"><?= htmlspecialchars($username) ?></div>
      <div class="profile-level">Player</div>

      <!-- Dropdown Menu -->
      <div class="dropdown" id="profileDropdown">
        <div class="points-section">
          <h4>Your Points</h4>
          <div class="points-row"><span>Beginner</span><span><?= htmlspecialchars($user['beginner_points'] ?? 0) ?></span></div>
          <div class="points-row"><span>Intermediate</span><span><?= htmlspecialchars($user['intermediate_points'] ?? 0) ?></span></div>
          <div class="points-row"><span>Advanced</span><span><?= htmlspecialchars($user['advanced_points'] ?? 0) ?></span></div>
        </div>
        <a href="profile.php">View Profile / History</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>

    <h2>Select Difficulty Level</h2>

    <div class="difficulty-wrap">
      <a class="big-btn btn-beginner" href="puzzle_match.php?level=beginner">Beginner</a>
      <a class="big-btn btn-intermediate" href="banana.php">Intermediate</a>
      <a class="big-btn btn-advanced" href="advanced.php">Advanced</a>
     </div>

  </div>

  <script>
    const icon = document.getElementById('profileIcon');
    const dropdown = document.getElementById('profileDropdown');

    icon.addEventListener('click', () => {
      const visible = dropdown.style.display === 'block';
      dropdown.style.display = visible ? 'none' : 'block';
    });

    document.addEventListener('click', (e) => {
      if(!dropdown.contains(e.target) && e.target !== icon){
        dropdown.style.display = 'none';
      }
    });
  </script>
</body>
</html>







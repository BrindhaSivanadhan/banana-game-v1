<?php
// Securely protect page
include 'config.php';
include 'session.php';
require_login(); // redirects to login.php if user is not logged in

$username = $_SESSION['username'];

// Fetch user points
$sql = "SELECT username, beginner_points, intermediate_points, advanced_points 
        FROM users ORDER BY (beginner_points + intermediate_points + advanced_points) DESC";
$result = $conn->query($sql);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Banana Bliss — Leaderboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    
    :root {
      --banana-yellow: #FFE135;
      --banana-dark: #FFB800;
      --banana-light: #FFF4A3;
      --banana-orange: #FFA726;
      --bg-dark: #3a2f1a;
      --bg-darker: #2d2415;
      --text-light: #fff9e6;
      --text-muted: #d4c5a0;
      --card-bg: rgba(255, 244, 163, 0.08);
    }

    html, body { 
      height: 100%; 
      margin: 0;
    }
    
    body {
      font-family: "Fredoka", sans-serif;
      background: linear-gradient(135deg, #2d2415 0%, #3a2f1a 50%, #4a3d20 100%);
      color: var(--text-light);
      min-height: 100vh;
      padding: 60px 20px 40px 20px;
      overflow-y: auto;
      position: relative;
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
      font-size: 30px;
      opacity: 0.1;
      animation: float 20s infinite ease-in-out;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      25% { transform: translateY(-30px) rotate(5deg); }
      50% { transform: translateY(-60px) rotate(-5deg); }
      75% { transform: translateY(-30px) rotate(3deg); }
    }

    /* Main container */
    .leaderboard-card {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 1000px;
      margin: 0 auto;
      background: var(--card-bg);
      backdrop-filter: blur(15px);
      border-radius: 24px;
      padding: 32px;
      border: 1px solid rgba(255, 225, 53, 0.2);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
      text-align: center;
      animation: slideIn 0.6s ease-out;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Header */
    .header-section {
      margin-bottom: 28px;
    }

    h1 {
      margin: 0 0 8px 0;
      font-size: 36px;
      font-weight: 700;
      background: linear-gradient(135deg, var(--banana-yellow) 0%, var(--banana-dark) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .subtitle {
      color: var(--text-muted);
      font-size: 14px;
      font-weight: 600;
    }

    /* Table container */
    .table-container {
      background: rgba(0, 0, 0, 0.15);
      border-radius: 16px;
      padding: 20px;
      border: 1px solid rgba(255, 225, 53, 0.1);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 0;
    }

    th, td {
      padding: 14px 12px;
      font-size: 15px;
      text-align: center;
    }

    th {
      background: rgba(255, 225, 53, 0.15);
      color: var(--banana-yellow);
      font-weight: 700;
      text-transform: uppercase;
      font-size: 12px;
      letter-spacing: 0.5px;
      border-bottom: 2px solid rgba(255, 225, 53, 0.3);
    }

    td {
      color: var(--text-light);
      font-weight: 600;
      border-bottom: 1px solid rgba(255, 225, 53, 0.1);
    }

    tr:hover td {
      background: rgba(255, 225, 53, 0.08);
    }

    /* Rank styling */
    td:first-child {
      font-weight: 700;
      color: var(--banana-yellow);
      font-size: 18px;
    }

    /* Top 3 special styling */
    tr:nth-child(1) td:first-child {
      color: #FFD700;
      font-size: 22px;
    }

    tr:nth-child(2) td:first-child {
      color: #C0C0C0;
      font-size: 20px;
    }

    tr:nth-child(3) td:first-child {
      color: #CD7F32;
      font-size: 18px;
    }

    /* Total column */
    td:last-child {
      font-weight: 700;
      color: var(--banana-yellow);
      font-size: 17px;
    }

    /* Empty state */
    .empty-state {
      padding: 40px 20px;
      color: var(--text-muted);
      font-size: 16px;
      font-weight: 600;
    }

    /* Back button */
    .btn-back {
      display: inline-block;
      margin-top: 24px;
      padding: 14px 28px;
      background: linear-gradient(135deg, var(--banana-yellow) 0%, var(--banana-dark) 100%);
      border-radius: 12px;
      color: #3a2f1a;
      text-decoration: none;
      font-weight: 700;
      font-size: 15px;
      box-shadow: 0 8px 20px rgba(255, 225, 53, 0.3);
      transition: all 0.3s ease;
    }

    .btn-back:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 28px rgba(255, 225, 53, 0.4);
    }

    /* Responsive */
    @media (max-width: 768px) {
      body { padding: 50px 15px 30px 15px; }
      .leaderboard-card { padding: 24px; }
      h1 { font-size: 28px; }
      .table-container { padding: 16px; }
      th, td { padding: 10px 8px; font-size: 13px; }
      th { font-size: 11px; }
      td:first-child { font-size: 16px; }
      tr:nth-child(1) td:first-child { font-size: 20px; }
      tr:nth-child(2) td:first-child { font-size: 18px; }
      tr:nth-child(3) td:first-child { font-size: 16px; }
      td:last-child { font-size: 15px; }
    }

    @media (max-width: 480px) {
      body { padding: 40px 10px 20px 10px; }
      .leaderboard-card { padding: 18px; }
      h1 { font-size: 24px; }
      .subtitle { font-size: 12px; }
      .table-container { padding: 12px; }
      th, td { padding: 8px 6px; font-size: 12px; }
      th { font-size: 10px; }
      .btn-back { padding: 12px 24px; font-size: 14px; }
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
  </div>

  <div class="leaderboard-card">
    <div class="header-section">
      <h1>🏆 Leaderboard 🏆</h1>
      <p class="subtitle">Top banana champions ranked by total points!</p>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Rank</th>
            <th>Player</th>
            <th>Beginner</th>
            <th>Intermediate</th>
            <th>Advanced</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows > 0) {
              $rank = 1;
              while ($row = $result->fetch_assoc()) {
                  $total = $row['beginner_points'] + $row['intermediate_points'] + $row['advanced_points'];
                  $rankIcon = '';
                  if ($rank === 1) $rankIcon = '🥇';
                  elseif ($rank === 2) $rankIcon = '🥈';
                  elseif ($rank === 3) $rankIcon = '🥉';
                  else $rankIcon = $rank;
                  
                  echo "<tr>
                          <td>{$rankIcon}</td>
                          <td>" . htmlspecialchars($row['username']) . "</td>
                          <td>{$row['beginner_points']}</td>
                          <td>{$row['intermediate_points']}</td>
                          <td>{$row['advanced_points']}</td>
                          <td>{$total}</td>
                        </tr>";
                  $rank++;
              }
          } else {
              echo "<tr><td colspan='6' class='empty-state'>🍌 No players found yet. Be the first champion!</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <a href="select_difficulty.php" class="btn-back">← Back to Game</a>
  </div>
</body>
</html>
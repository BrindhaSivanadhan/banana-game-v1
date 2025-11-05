<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
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
  <title>Leaderboard — Banana Game</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(180deg, #ffe7ec, #e7f0ff);
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .leaderboard-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      padding: 40px;
      text-align: center;
      width: 800px;
      max-width: 95%;
    }
    h1 {
      font-size: 30px;
      color: #333;
      margin-bottom: 20px;
      background: linear-gradient(90deg, #ffd6a5, #bde0fe);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      border-radius: 12px;
      overflow: hidden;
    }
    th, td {
      padding: 14px;
      font-size: 16px;
      text-align: center;
    }
    th {
      background: linear-gradient(90deg, #cde3ff, #ffe6e6);
      color: #333;
      font-weight: 700;
    }
    tr:nth-child(even) {
      background: #f9f9f9;
    }
    tr:hover {
      background: #fff9f0;
      transition: 0.2s ease;
    }
    .btn-back {
      display: inline-block;
      margin-top: 25px;
      padding: 10px 20px;
      background: #bde0fe;
      border-radius: 10px;
      color: #333;
      text-decoration: none;
      font-weight: 600;
      transition: 0.2s ease;
    }
    .btn-back:hover {
      background: #a6d2fc;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>
  <div class="leaderboard-card">
    <h1>🏆 Leaderboard 🏆</h1>

    <table>
      <tr>
        <th>Rank</th>
        <th>Username</th>
        <th>Beginner Points</th>
        <th>Intermediate Points</th>
        <th>Advanced Points</th>
        <th>Total</th>
      </tr>
      <?php
      if ($result->num_rows > 0) {
          $rank = 1;
          while ($row = $result->fetch_assoc()) {
              $total = $row['beginner_points'] + $row['intermediate_points'] + $row['advanced_points'];
              echo "<tr>
                      <td>{$rank}</td>
                      <td>" . htmlspecialchars($row['username']) . "</td>
                      <td>{$row['beginner_points']}</td>
                      <td>{$row['intermediate_points']}</td>
                      <td>{$row['advanced_points']}</td>
                      <td><b>{$total}</b></td>
                    </tr>";
              $rank++;
          }
      } else {
          echo "<tr><td colspan='6'>No players found yet.</td></tr>";
      }
      ?>
    </table>

    <a href="select_difficulty.php" class="btn-back">⬅ Back to Game</a>
  </div>
</body>
</html>


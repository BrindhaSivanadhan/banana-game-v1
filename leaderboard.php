<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
$sql = "SELECT username, beginner_points, intermediate_points, advanced_points,
        (COALESCE(beginner_points,0)+COALESCE(intermediate_points,0)+COALESCE(advanced_points,0)) as total
        FROM users
        ORDER BY total DESC, username ASC
        LIMIT 50";
$res = $conn->query($sql);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Leaderboard</title>
  <style>
    body{font-family:Poppins,Segoe UI,Arial;background:linear-gradient(180deg,#fffceb,#e6f7ff);margin:0;padding:40px}
    .card{background:#fff;border-radius:12px;padding:20px;max-width:900px;margin:0 auto;box-shadow:0 10px 30px rgba(0,0,0,0.06)}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{padding:10px;border-bottom:1px solid #f0f0f0;text-align:left}
    th{background:#f7fafc}
    .me{font-weight:800;color:#2b5a2b}
  </style>
</head>
<body>
  <div class="card">
    <h2>Leaderboard</h2>
    <table>
      <thead><tr><th>#</th><th>Username</th><th>Beginner</th><th>Intermediate</th><th>Advanced</th><th>Total</th></tr></thead>
      <tbody>
        <?php $i=1; while($row = $res->fetch_assoc()): ?>
          <tr class="<?= ($row['username'] === $_SESSION['username']) ? 'me' : '' ?>">
            <td><?= $i ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= intval($row['beginner_points']) ?></td>
            <td><?= intval($row['intermediate_points']) ?></td>
            <td><?= intval($row['advanced_points']) ?></td>
            <td><?= intval($row['total']) ?></td>
          </tr>
        <?php $i++; endwhile; ?>
      </tbody>
    </table>
    <p style="margin-top:12px"><a href="select_difficulty.php">Back</a></p>
  </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Banana Game — Home</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Welcome, <?=htmlspecialchars($_SESSION['username'])?>!</h1>
    <p>
      <a class="big-link" href="select_difficulty.php">Play Puzzle</a>
      <a class="big-link" href="banana.php">Play Banana Game</a>
      <a class="big-link" href="logout.php">Logout</a>
    </p>
  </div>
</body>
</html>
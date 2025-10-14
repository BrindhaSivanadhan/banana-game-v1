<?php
session_start();
if (isset($_SESSION['username'])) {
    echo "Hello, " . htmlspecialchars($_SESSION['username']) . ". <a href='select_difficulty.php'>Play</a>";
} else {
    echo "<a href='login.php'>Login</a> or <a href='register.php'>Register</a>";
}

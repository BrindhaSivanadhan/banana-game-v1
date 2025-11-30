<?php
// logout.php — securely log out user
include 'session.php';  // include our session handling

logout_user();           // safely destroy session and cookies
header("Location: login.php?loggedout=1"); // redirect to login page with flag
exit();


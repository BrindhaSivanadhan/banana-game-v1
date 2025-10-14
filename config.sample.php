<?php
// config.sample.php
// Copy this file to config.php and update DB credentials locally
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';           // <-- put your MySQL root password if any
$DB_NAME = 'banana_game_v1';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_errno) {
    // in production don't show details; for local dev this is helpful
    die("Database connection failed: (" . $conn->connect_errno . ") " . $conn->connect_error);
}

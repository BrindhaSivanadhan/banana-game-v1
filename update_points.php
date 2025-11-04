<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// Make sure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$level = $data['level'] ?? '';
$points = intval($data['points'] ?? 0);
$username = $_SESSION['username'];

// Make sure level is valid
if (!in_array($level, ['beginner', 'intermediate', 'advanced'])) {
    echo json_encode(["error" => "Invalid level"]);
    exit;
}

// Choose correct column
$column = $level . '_points';

// Add points for this user
$sql = "UPDATE users SET $column = $column + ? WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $points, $username);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Points updated"]);
} else {
    echo json_encode(["error" => "No rows updated"]);
}
?>

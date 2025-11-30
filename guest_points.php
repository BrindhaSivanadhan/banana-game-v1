<?php
include 'session.php';

header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);
$level = isset($payload['level']) ? strtolower($payload['level']) : '';
$points = isset($payload['points']) ? (int)$payload['points'] : 0;
$allowedLevels = ['beginner', 'intermediate', 'advanced'];

if (!in_array($level, $allowedLevels, true) || $points <= 0) {
    echo json_encode(['error' => 'Invalid guest points payload']);
    exit;
}

if (!isset($_SESSION['guest_pending_points'])) {
    $_SESSION['guest_pending_points'] = [];
}

if (!isset($_SESSION['guest_pending_points'][$level])) {
    $_SESSION['guest_pending_points'][$level] = 0;
}

$_SESSION['guest_pending_points'][$level] += $points;

echo json_encode(['success' => true]);


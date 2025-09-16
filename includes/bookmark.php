<?php
session_start();
include '../includes/db_connection.php';
header('Content-Type: application/json');

$currentUserId = $_SESSION['user_id'] ?? null;
$listingsId    = intval($_POST['listings_id'] ?? 0);
$postUserId    = intval($_POST['user_id'] ?? 0);

if (!$currentUserId || $currentUserId !== $postUserId) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

if ($listingsId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid listing']);
    exit;
}

$stmt = $pdo->prepare("INSERT IGNORE INTO bookmark (user_id, listings_id) VALUES (?, ?)");
$success = $stmt->execute([$currentUserId, $listingsId]);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Bookmarked successfully' : 'Already bookmarked'
]);

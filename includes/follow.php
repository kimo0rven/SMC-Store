<?php
session_start();
include './db_connection.php';

$currentUserId = $_SESSION['user_id'] ?? null;
$postUserId    = intval($_POST['user_id'] ?? 0);
$sellerId      = intval($_POST['seller_id'] ?? 0);

if (!$currentUserId || $currentUserId !== $postUserId) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

if ($sellerId <= 0 || $sellerId === $currentUserId) {
    echo json_encode(['success' => false, 'message' => 'Invalid seller']);
    exit;
}

$stmt = $pdo->prepare("
    INSERT IGNORE INTO follow (follower_id, followed_id)
    VALUES (?, ?)
");
$success = $stmt->execute([$currentUserId, $sellerId]);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Followed successfully' : 'Already following'
]);


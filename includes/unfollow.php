<?php
session_start();
include '../includes/db_connection.php';

$currentUserId = $_SESSION['user_id'] ?? null;
$sellerId      = intval($_POST['seller_id'] ?? 0);

if (!$currentUserId || $sellerId <= 0 || $sellerId === $currentUserId) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM follow WHERE follower_id = ? AND followed_id = ?");
$success = $stmt->execute([$currentUserId, $sellerId]);

echo json_encode(['success' => $success]);

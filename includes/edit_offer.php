<?php
session_start();
header('Content-Type: application/json; charset=utf-8');


if (empty($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

require __DIR__ . '/db_connection.php';

$loggedInUserId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$conversationId = isset($_POST['conversation_id']) ? (int) $_POST['conversation_id'] : 0;
$newOffer       = isset($_POST['new_offer']) ? (float) $_POST['new_offer'] : 0;

if ($conversationId <= 0 || $newOffer <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

$sql = "SELECT seller_id, buyer_id 
        FROM conversation 
        WHERE conversation_id = :cid
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':cid' => $conversationId]);
$convo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$convo) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Conversation not found']);
    exit();
}

if ($loggedInUserId !== (int)$convo['seller_id'] && $loggedInUserId !== (int)$convo['buyer_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

$update = "UPDATE conversation
           SET current_offer_amount = :offer, is_read = 0
           WHERE conversation_id = :cid";
$stmt = $pdo->prepare($update);
$stmt->execute([
    ':offer' => $newOffer,
    ':cid'   => $conversationId
]);

$insertMsg = "INSERT INTO conversation_message 
              (conversation_id, sender_id, message, is_read)
              VALUES (:cid, :uid, :msg, 0)";
$msgText = "Offer updated to PHP " . number_format($newOffer, 2);
$stmt = $pdo->prepare($insertMsg);
$stmt->execute([
    ':cid' => $conversationId,
    ':uid' => $loggedInUserId,
    ':msg' => $msgText
]);

echo json_encode(['success' => true, 'new_offer' => $newOffer]);
exit;



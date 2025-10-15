<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . "/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$buyerId   = (int)$_SESSION["user_id"];
$rawData   = json_decode(file_get_contents("php://input"), true);
$listingId = isset($rawData["listings_id"]) ? (int)$rawData["listings_id"] : 0;
$offerAmount = isset($rawData["offer_amount"]) ? (float)$rawData["offer_amount"] : 0;
$action    = $rawData["action"] ?? 'make_offer';

if ($listingId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid listing']);
    exit;
}

//CHECK IF LISTING EXISTS
$listingSql = "SELECT listing_owner_id AS seller_id, price 
                FROM listings 
                WHERE listings_id = :listing_id";
$listingStmt = $pdo->prepare($listingSql);
$listingStmt->execute(["listing_id" => $listingId]);
$listingData = $listingStmt->fetch(PDO::FETCH_ASSOC);

if (!$listingData) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Listing not found']);
    exit;
}

$sellerId     = (int)$listingData["seller_id"];
$listingPrice = (float)$listingData["price"];

//CHECK IF SAME BUYER AND SELLER ID
if ($sellerId === $buyerId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cannot make offer on your own listing']);
    exit;
}

$finalOfferAmount = $offerAmount > 0 ? $offerAmount : $listingPrice;

//CHECK IF AN EXISTING CONVERSATION EXISTS
$exactSql = "SELECT conversation_id 
                FROM conversation 
                WHERE ((seller_id = :seller_id AND buyer_id = :buyer_id) 
                    OR (seller_id = :buyer_id AND buyer_id = :seller_id))
                AND listings_id = :listing_id
                LIMIT 1";
$exactStmt = $pdo->prepare($exactSql);
$exactStmt->execute([
    "seller_id"   => $sellerId,
    "buyer_id"    => $buyerId,
    "listing_id"  => $listingId,
]);
$exactConvo = $exactStmt->fetch(PDO::FETCH_ASSOC);

//IF A CONVERSATION ALREADY EXISTS W/ THE SAME SELLER, BUYER AND LISTING
if ($exactConvo) {
    $conversationId = $exactConvo["conversation_id"];

    $updateOfferSql = "UPDATE conversation 
                        SET current_offer_amount = :offer, is_read = 0
                        WHERE conversation_id = :convo_id";
    $updateOfferStmt = $pdo->prepare($updateOfferSql);
    $updateOfferStmt->execute([
        "offer"    => $finalOfferAmount,
        "convo_id" => $conversationId,
    ]);

    if ($action !== 'create_chat') {
        $offerMsg = "I made an offer of PHP " . number_format($finalOfferAmount, 2);
        $insertMsgSql = "INSERT INTO conversation_message 
                            (conversation_id, sender_id, message, is_read, created_at)
                            VALUES (:conversation_id, :sender_id, :message, 0, NOW())";
        $insertMsgStmt = $pdo->prepare($insertMsgSql);
        $insertMsgStmt->execute([
            "conversation_id" => $conversationId,
            "sender_id"       => $buyerId,
            "message"         => $offerMsg,
        ]);
    }

    echo json_encode(['success' => true, 'conversation_id' => $conversationId, 'new_offer' => $finalOfferAmount]);
    exit;
}

$anySql = "SELECT conversation_id 
            FROM conversation 
            WHERE (seller_id = :seller_id AND buyer_id = :buyer_id) 
                OR (seller_id = :buyer_id AND buyer_id = :seller_id)
            LIMIT 1";
$anyStmt = $pdo->prepare($anySql);
$anyStmt->execute([
    "seller_id" => $sellerId,
    "buyer_id"  => $buyerId,
]);
$anyConvo = $anyStmt->fetch(PDO::FETCH_ASSOC);

if ($anyConvo) {
    $conversationId = $anyConvo["conversation_id"];

    $updateSql = "UPDATE conversation 
                    SET listings_id = :new_listing_id, current_offer_amount = :offer, is_read = 0
                    WHERE conversation_id = :convo_id";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        "new_listing_id" => $listingId,
        "offer"          => $finalOfferAmount,
        "convo_id"       => $conversationId,
    ]);

    if ($action !== 'create_chat') {
        $offerMsg = "I made an offer of PHP " . number_format($finalOfferAmount, 2);
        $insertMsgSql = "INSERT INTO conversation_message 
                            (conversation_id, sender_id, message, is_read, created_at)
                            VALUES (:conversation_id, :sender_id, :message, 0, NOW())";
        $insertMsgStmt = $pdo->prepare($insertMsgSql);
        $insertMsgStmt->execute([
            "conversation_id" => $conversationId,
            "sender_id"       => $buyerId,
            "message"         => $offerMsg,
        ]);
    }

    echo json_encode(['success' => true, 'conversation_id' => $conversationId, 'new_offer' => $finalOfferAmount]);
    exit;
}

$insertSql = "INSERT INTO conversation 
                (seller_id, buyer_id, listings_id, current_offer_amount, is_read, created_at)
                VALUES (:seller_id, :buyer_id, :listings_id, :offer, 0, NOW())";
$insertStmt = $pdo->prepare($insertSql);
$insertStmt->execute([
    "seller_id"   => $sellerId,
    "buyer_id"    => $buyerId,
    "listings_id" => $listingId,
    "offer"       => $finalOfferAmount,
]);
$conversationId = $pdo->lastInsertId();

if ($action !== 'create_chat') {
    $offerMsg = "Started a chat and made an offer of PHP " . number_format($finalOfferAmount, 2);
    $insertMsgSql = "INSERT INTO conversation_message 
                        (conversation_id, sender_id, message, is_read, created_at)
                        VALUES (:conversation_id, :sender_id, :message, 0, NOW())";
    $insertMsgStmt = $pdo->prepare($insertMsgSql);
    $insertMsgStmt->execute([
        "conversation_id" => $conversationId,
        "sender_id"       => $buyerId,
        "message"         => $offerMsg,
    ]);
}

echo json_encode(['success' => true, 'conversation_id' => $conversationId, 'new_offer' => $finalOfferAmount]);
exit;

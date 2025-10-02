<?php
session_start();

if (empty($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: index.php");
    exit();
}

require './includes/db_connection.php';

if (!isset($_GET['conversation_id'])) {
    die("No conversation selected.");
}
$conversationId = (int) $_GET['conversation_id'];

$convoQuery = "
    SELECT 
        c.conversation_id,
        c.created_at,
        u1.user_id     AS seller_id,
        u1.first_name  AS seller_first_name,
        u1.last_name   AS seller_last_name,
        u2.user_id     AS buyer_id,
        u2.first_name  AS buyer_first_name,
        u2.last_name   AS buyer_last_name,
        l.listings_id,
        l.name         AS listing_name,
        l.price        AS listing_price
    FROM conversation c
    INNER JOIN user u1 ON c.seller_id = u1.user_id
    INNER JOIN user u2 ON c.buyer_id = u2.user_id
    LEFT JOIN listings l ON c.listings_id = l.listings_id
    WHERE c.conversation_id = :conversation_id
    LIMIT 1
";
$stmt = $pdo->prepare($convoQuery);
$stmt->execute([':conversation_id' => $conversationId]);
$conversation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$conversation) {
    die("Conversation not found.");
}

$loggedInUserId = (int) $_SESSION['user_id'];
if ($loggedInUserId !== (int) $conversation['seller_id'] && $loggedInUserId !== (int) $conversation['buyer_id']) {
    die("You are not a participant in this conversation.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message !== '') {
        $insertMsg = "
            INSERT INTO conversation_message (conversation_id, sender_id, message, is_read)
            VALUES (:conversation_id, :sender_id, :message, 0)
        ";
        $stmt = $pdo->prepare($insertMsg);
        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':sender_id'       => $loggedInUserId,
            ':message'         => $message
        ]);

        $updateConvo = "
            UPDATE conversation
            SET is_read = 0
            WHERE conversation_id = :cid
        ";
        $stmt = $pdo->prepare($updateConvo);
        $stmt->execute([':cid' => $conversationId]);

        header("Location: conversation.php?conversation_id=" . $conversationId);
        exit();
    }
}


$msgQuery = "
    SELECT 
        m.message_id, 
        m.message, 
        m.created_at,
        u.user_id, 
        u.first_name, 
        u.last_name
    FROM conversation_message m
    INNER JOIN user u ON m.sender_id = u.user_id
    WHERE m.conversation_id = :conversation_id
    ORDER BY m.created_at ASC
";
$stmt = $pdo->prepare($msgQuery);
$stmt->execute([':conversation_id' => $conversationId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($loggedInUserId === (int) $conversation['seller_id']) {
    $otherName = $conversation['buyer_first_name'] . ' ' . $conversation['buyer_last_name'];
} else {
    $otherName = $conversation['seller_first_name'] . ' ' . $conversation['seller_last_name'];
}

$update = "
    UPDATE conversation_message
    SET is_read = 1
    WHERE conversation_id = :cid
      AND sender_id != :uid
";
$stmt = $pdo->prepare($update);
$stmt->execute([
    ':cid' => $conversationId,
    ':uid' => $_SESSION['user_id']
]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation | Michaelite Store</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">
</head>
<body>

<header>
    <?php include './public/components/header.php' ?>
</header>

<main class="inbox-main">
    <div class="inbox-right-side" style="flex: 1;">
        
        <!-- Back button -->
        <div style="padding:1rem; border-bottom:1px solid #eee; background:#fafafa;">
            <a href="chat.php" 
               style="display:inline-block; padding:.5rem 1rem; background:#0078d7; color:#fff; 
                      border-radius:6px; text-decoration:none; font-size:.9rem;">
                ← Back to Chats
            </a>
        </div>

        <div class="conversation-header">
            <h3>
                Chat with <?= htmlspecialchars($otherName) ?>
            </h3>
            <?php if (!empty($conversation['listing_name'])): ?>
                <p>
                    Listing: <?= htmlspecialchars($conversation['listing_name']) ?>
                    — $<?= htmlspecialchars($conversation['listing_price']) ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="conversation-messages" id="conversation-messages">
            <?php if (empty($messages)): ?>
                <p style="padding:1rem;color:#666;">No messages yet. Say hello!</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= ($loggedInUserId == $msg['user_id']) ? 'my-message' : 'their-message' ?>">
                        <!-- <strong><?= htmlspecialchars($msg['first_name'].' '.$msg['last_name']) ?>:</strong> -->
                        <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                        <small><?= htmlspecialchars($msg['created_at']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="conversation-input" style="padding:1rem;border-top:1px solid #eee;background:#fafafa;display:flex;gap:.5rem;">
            <form action="conversation.php?conversation_id=<?= $conversationId ?>" method="post" style="display:flex;gap:.5rem;flex:1;">
                <input 
                    type="text" 
                    name="message" 
                    placeholder="Type your message..." 
                    required 
                    style="flex:1;padding:.6rem .8rem;border:1px solid #ccc;border-radius:8px;outline:none;"
                >
                <button type="submit" style="padding:.6rem 1rem;border:none;background:#0078d7;color:#fff;border-radius:8px;cursor:pointer;">
                    Send
                </button>
            </form>
        </div>
    </div>
</main>


<footer>
    <?php include './public/components/footer.php' ?>
</footer>

<script src="/public/javascripts/category_dropdown.js"></script>
<script>
// Optional: auto-scroll to bottom on load
const container = document.getElementById('conversation-messages');
if (container) container.scrollTop = container.scrollHeight;
</script>
</body>
</html>

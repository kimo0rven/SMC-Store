<?php
session_start();

if (empty($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require './includes/db_connection.php';

$convoQuery = "
    SELECT c.conversation_id,
           c.seller_id,
           c.buyer_id,
           l.name AS listing_name,
           l.price AS listing_price,
           u1.first_name AS seller_first_name,
           u1.last_name  AS seller_last_name,
           u2.first_name AS buyer_first_name,
           u2.last_name  AS buyer_last_name,
           SUM(CASE WHEN m.is_read = 0 AND m.sender_id != :uid THEN 1 ELSE 0 END) AS unread_count
    FROM conversation c
    JOIN user u1 ON c.seller_id = u1.user_id
    JOIN user u2 ON c.buyer_id = u2.user_id
    LEFT JOIN listings l ON c.listings_id = l.listings_id
    LEFT JOIN conversation_message m ON c.conversation_id = m.conversation_id
    WHERE c.seller_id = :uid OR c.buyer_id = :uid
    GROUP BY c.conversation_id
    ORDER BY c.created_at ASC
";

$stmt = $pdo->prepare($convoQuery);
$stmt->execute([':uid' => $_SESSION['user_id']]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chats | Michaelite Store</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">
</head>
<body>

<header>
    <?php include './public/components/header.php' ?>
</header>

<main class="inbox-main">
    <section class="inbox">
        <div class="inbox-left-side">
            <button>Message</button>
            <button>Unread</button>
        </div>

        <div class="inbox-right-side">
            <div class="inbox-header">Inbox</div>
            <?php if (!empty($conversations)): ?>
                <?php foreach($conversations as $conv): ?>
                    <?php
                        if ($_SESSION['user_id'] == $conv['seller_id']) {
                            $otherName = $conv['buyer_first_name'] . ' ' . $conv['buyer_last_name'];
                        } else {
                            $otherName = $conv['seller_first_name'] . ' ' . $conv['seller_last_name'];
                        }
                    ?>
                    <a href="conversation.php?conversation_id=<?= $conv['conversation_id'] ?>">
                        <div class="inbox-message-display <?= ($conv['unread_count'] > 0) ? 'unread' : '' ?>">
                            <div>
                                <img height="45" width="45" src="/public/assets/images/avatars/1.jpg" alt="">
                            </div>
                            <div>
                                <?= htmlspecialchars($otherName) ?><br>
                                <small><?= htmlspecialchars($conv['listing_name']) ?> - $<?= $conv['listing_price'] ?></small>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="badge"><?= $conv['unread_count'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach ?>
            <?php else: ?>
                <p>No conversations found.</p>
            <?php endif; ?>
        </div>
    </section>
</main>



<footer>
    <?php include './public/components/footer.php' ?>
</footer>

<script src="./public/javascripts/category_dropdown.js"></script>


</script>

</body>
</html>

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
        c.current_offer_amount,
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/public/assets/images/conversation/' . $conversationId . "/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = '/public/assets/images/conversation/' . $conversationId . "/" . $filename;
        }
    }

    if ($message !== '' || $imagePath !== null) {
        $insertMsg = "
            INSERT INTO conversation_message (conversation_id, sender_id, message, image_path, is_read)
            VALUES (:conversation_id, :sender_id, :message, :image_path, 0)
        ";
        $stmt = $pdo->prepare($insertMsg);
        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':sender_id' => $loggedInUserId,
            ':message' => $message,
            ':image_path' => $imagePath,
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
        m.image_path,
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
    ':uid' => $_SESSION['user_id'],
]);

if ($conversation['current_offer_amount'] > 0) {
    $conversation['listing_price'] = $conversation['current_offer_amount'];
}
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
    <?php include './public/components/header.php'; ?>
</header>

<main class="inbox-main">
    <div class="inbox-right-side" style="flex: 1;">

        <div style="display: flex; align-items:center; justify-items:center; gap: 1rem; padding: 0.5rem 0">
            <div>
                <button id="back-to-chat" class="chat-btn">
                    ← Back
                </button>
            </div>

            <?php if ($conversation['seller_id'] != $_SESSION['user_id']): ?>
            <div id="offer-section">
                <button id="edit_offer_btn" class="chat-btn">Edit Offer</button>

                <div id="offer-edit-form" style="display:none; margin-top:.5rem;">
                    <input type="number" id="new-offer-field"
                        placeholder="Enter new offer"
                        min="1"
                        value="<?= htmlspecialchars($conversation['listing_price']) ?>">
                </div>
            </div>

            <?php endif; ?>

            <?php if ($conversation['seller_id'] != $_SESSION['user_id']): ?>
                <div>
                    <form action="/order_request.php" method="post" style="display:inline;">
                    <input type="hidden" name="listing_id" value="<?= (int)$conversation['listings_id'] ?>">
                    <input type="hidden" name="conversation_id" value="<?= (int)$conversation['conversation_id'] ?>">
                    <button type="submit" id="create_order_btn" class="chat-btn">
                        Request Order
                    </button>
                    </form>

                </div>
            <?php endif; ?>
            </div>

        <div class="conversation-header">
            <span style="font-size: 1.5rem; font-weight: bold">
                Chat with <?= htmlspecialchars($otherName) ?>
            </span>
            <?php if (!empty($conversation['listing_name'])): ?>
                <span><a href="/listings.php?=<?= htmlspecialchars($conversation['listings_id']) ?>"><?= htmlspecialchars($conversation['listing_name']) ?></a>
                    — PHP <?= htmlspecialchars($conversation['listing_price']) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="conversation-messages" id="conversation-messages">
            <?php if (empty($messages)): ?>
                <p style="padding:1rem;color:#666;">No messages yet. Say hello!</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $loggedInUserId == $msg['user_id'] ? 'my-message' : 'their-message' ?>">
                        <?php if (!empty($msg['message'])): ?>
                            <p style="margin: 0;"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($msg['image_path'])): ?>
                            <div class="chat-image">
                                <img src="<?= htmlspecialchars($msg['image_path']) ?>" alt="chat image">
                            </div>
                        <?php endif; ?>

                        <small><?= htmlspecialchars($msg['created_at']) ?></small>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>

        <div class="conversation-input" style="padding:1rem;border-top:1px solid #eee;background:#fafafa;display:flex;gap:.5rem;">
            <form action="conversation.php?conversation_id=<?= $conversationId ?>" 
                method="post" 
                enctype="multipart/form-data" 
                class="chat-form">

                <input type="file" name="image" id="image-input" accept="image/*">
                <label for="image-input" class="upload-icon" title="Attach image">
                    📎
                </label>

                <input 
                    type="text" 
                    name="message" 
                    placeholder="Type your message..." 
                    required
                >

                <button type="submit" class="send-btn">Send</button>
            </form>

        </div>
    </div>

</main>

<footer>
    <?php include './public/components/footer.php'; ?>
</footer>

<script src="/public/javascripts/category_dropdown.js"></script>
<script>
    document.getElementById('back-to-chat').addEventListener('click', () => {
    window.location.href = '/chat.php';
    });

    const editBtn   = document.getElementById('edit_offer_btn');
    const formDiv   = document.getElementById('offer-edit-form');
    const input     = document.getElementById('new-offer-field');
    const openBtn   = document.getElementById('create_order_btn');
    const dialog    = document.getElementById('orderDialog');
    const container = document.getElementById('conversation-messages');

    let editing = false;
    const conversationId = <?= (int) $conversationId ?>;

    if (editBtn) {
    editBtn.addEventListener('click', () => {
        if (!editing) {
        formDiv.style.display = 'block';
        if (openBtn) openBtn.style.display = 'none';
        editBtn.textContent = 'Update Offer';
        editing = true;
        } else {
        const newOffer = input.value;
        if (!newOffer || newOffer <= 0) {
            alert("Please enter a valid offer amount.");
            return;
        }

        editBtn.disabled = true;
        editBtn.textContent = 'Updating…';

        fetch('/includes/edit_offer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `conversation_id=${encodeURIComponent(conversationId)}&new_offer=${encodeURIComponent(newOffer)}`
        })
        .then(res => {
            if (!res.ok) throw new Error("HTTP " + res.status);
            return res.json();
        })
        .then(data => {
            if (data.success) {
            console.log("Offer updated:", data.new_offer);

            formDiv.style.display = 'none';
            if (openBtn) openBtn.style.display = 'inline-block';
            editBtn.textContent = 'Edit Offer';
            editing = false;

            const headerPrice = document.querySelector('.conversation-header span:last-child');
            if (headerPrice) {
                headerPrice.innerHTML = headerPrice.innerHTML.replace(/PHP\s[\d,.]+/, 'PHP ' + data.new_offer);
            }

            const container = document.getElementById('conversation-messages');
            if (container) {
                const msg = document.createElement('div');
                msg.className = 'message system-message';
                msg.innerHTML = `<p style="margin:0;color:#555;font-style:italic;">
                Offer updated to PHP ${data.new_offer}
                </p><small>just now</small>`;
                container.appendChild(msg);

                container.scrollTop = container.scrollHeight;
            }
            } else {
            alert(data.error || "Failed to update offer");
            }
        })
        .catch(err => {
            console.error("Fetch error:", err);
            alert("Network or server error");
        })
        .finally(() => {
            editBtn.disabled = false;
        });
        }
    });
    }

    if (openBtn && dialog) {
    openBtn.addEventListener('click', () => dialog.showModal());
    dialog.addEventListener('click', (event) => {
        const rect = dialog.getBoundingClientRect();
        const isInDialog =
        event.clientX >= rect.left &&
        event.clientX <= rect.right &&
        event.clientY >= rect.top &&
        event.clientY <= rect.bottom;
        if (!isInDialog) dialog.close();
    });
    }

    if (container) container.scrollTop = container.scrollHeight;

    document.addEventListener('click', (e) => {
    if (e.target.tagName === 'IMG' && e.target.closest('.chat-image')) {
        const src = e.target.src;
        const overlay = document.createElement('div');
        overlay.classList.add('chat-image-overlay');
        overlay.innerHTML = `<img src="${src}" alt="enlarged image">`;
        overlay.addEventListener('click', () => overlay.remove());
        document.body.appendChild(overlay);
    }
    });
</script>

</body>
</html>
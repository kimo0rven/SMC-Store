<?php
session_start();
require './includes/db_connection.php';

$userID = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_SESSION['user_id'];
$type = $_GET['type'] ?? 'listings';

$loggedUser = $userID == (int)$_SESSION['user_id'];

$categQuery = "SELECT * FROM user WHERE user_id = ?";
$stmt = $pdo->prepare($categQuery);
$stmt->execute([$userID]);
$userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userDetails) {
    $errorMessage = "User not found.";
}

function fetchListingsWithImages(PDO $pdo, string $sql, array $params = []): array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $listings = [];
    foreach ($rows as $row) {
        $id = $row['listings_id'];
        $listings[$id] ??= [
            'listings_id'    => $row['listings_id'],
            'name'           => $row['name'],
            'brand'          => $row['brand'],
            'price'          => $row['price'],
            'description'    => $row['description'],
            'item_condition' => $row['item_condition'] ?? null,
            'date_created'   => $row['date_created'] ?? null,
            'images'         => []
        ];
        if ($row['image_url']) {
            $listings[$id]['images'][] = [
                'url'        => $row['image_url'],
                'is_primary' => $row['is_primary']
            ];
        }
    }
    return $listings;
}

$listings = [];
$stmt = $pdo->prepare("SELECT * FROM listings WHERE listing_owner_id = ?");
$stmt->execute([$userID]);
$listingsId = $stmt->fetchAll(PDO::FETCH_COLUMN);

$listings = [];

if ($type === 'listings') {
    $stmt = $pdo->prepare("SELECT * FROM listings WHERE listing_owner_id = ?");
    $stmt->execute([$userID]);
    $listingsId = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($listingsId) {
        $ph = implode(',', array_fill(0, count($listingsId), '?'));
        $sql = "
            SELECT l.listings_id, l.name, l.brand, l.price, l.description,
                    l.item_condition, li.image_url, li.is_primary, li.date_created
            FROM listings l
            LEFT JOIN listing_images li ON l.listings_id = li.listings_id
            WHERE l.listings_id IN ($ph)
            ORDER BY FIELD(l.listings_id, $ph)
        ";
        $listings = fetchListingsWithImages($pdo, $sql, [...$listingsId, ...$listingsId]);
    }

} elseif ($type === 'likes') {
    $sql = "
        SELECT l.listings_id, l.name, l.brand, l.price, l.description,
                l.item_condition, li.image_url, li.is_primary, li.date_created
        FROM bookmark lk
        INNER JOIN listings l ON lk.listings_id = l.listings_id
        LEFT JOIN listing_images li ON l.listings_id = li.listings_id
        WHERE lk.user_id = ?
    ";
    $listings = fetchListingsWithImages($pdo, $sql, [$userID]);
}


$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM review
    WHERE seller_id = ?
");
$stmt->execute([$userID]);
$reviewCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM orders o
    INNER JOIN listings l ON o.listings_id = l.listings_id
    WHERE l.listing_owner_id = ?
      AND o.status = 'completed'
");
$stmt->execute([$userID]);
$soldCount = $stmt->fetchColumn();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell | Michaelite Store</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">
</head>
<body>
<header>
    <?php include './public/components/header.php' ?>
</header>

<main>
    <div class="flex column" style="width: 90%">
        <div class="user-profile-container">
        <div>
            <h2 style="font-size:2.25rem;text-transform:uppercase;margin:0">Seller Page</h2>
        </div>

        <?php if (isset($errorMessage)): ?>
            <div class="user-profile-left">
            <p><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php else: ?>

            <div class="user-profile-left">
            <div class="user-profile-header">
                <img src="public/assets/images/avatars/1.jpg" alt="">
                <span class="user-profile-header-name">
                <?= htmlspecialchars($userDetails['first_name']) ?>
                </span>
            </div>

            <div class="user-profile-header-middle">
                <div>
                <?= htmlspecialchars($soldCount) ?>
                <?= ($soldCount <= 1) ? 'item sold' : 'items sold' ?>
                </div>
                <div>|</div>
                <div>
                <?= htmlspecialchars($reviewCount) ?>
                <?= ($reviewCount <= 1) ? 'review' : 'reviews' ?>
                </div>
            </div>

            <div class="user-header-right-actions">
                <?php if ((int)$userID != $_SESSION['user_id']): ?>
                <div>
                    <span>
                    <button class="chat-btn"
                            data-seller="<?= (int)$userID ?>"
                            data-user="<?= (int)$_SESSION['user_id'] ?>">
                        Follow
                    </button>
                    </span>
                </div>
                <?php endif ?>
                <div>
                <span id="share-btn">
                    <img src="./public/assets/images/icons/share_icon.png" alt="Share">
                </span>
                </div>
            </div>
            </div>

            <?php if ($userID == $_SESSION['user_id']): ?>
            <div class="user-profile-tabs">
                <a href="?id=<?= $userID ?>&type=listings">
                <button class="chat-btn <?= ($type === 'listings') ? 'active' : '' ?>">My Listings</button>
                </a>
                <a href="?id=<?= $userID ?>&type=likes">
                <button class="chat-btn <?= ($type === 'likes') ? 'active' : '' ?>">My Likes</button>
                </a>
            </div>
            <?php endif; ?>

            <div class="user-profile-right-wrapper">
            <div class="user-profile-right-display">
                <?php if (!empty($listings)): ?>
                <?php foreach ($listings as $listing): ?>
                    <?php include "./public/components/listing_card.php"; ?>
                <?php endforeach; ?>
                <?php else: ?>
                <p>No products found.</p>
                <?php endif; ?>
            </div>
            </div>

        <?php endif; ?>
        </div>
    </div>
</main>



<footer>
    <?php include './public/components/footer.php' ?>
</footer>

<script>
    <?php include './public/javascripts/share_btn.js' ?>
</script>
</body>
</html>

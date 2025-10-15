<?php
session_start();
require './includes/db_connection.php';

if (empty($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: index.php");
    exit();
}

$userID = (int)$_SESSION['user_id'];

// Fetch my listings
$sql = "
    SELECT l.listings_id, l.name, l.price, l.brand, l.description,
           li.image_url, li.is_primary
    FROM listings l
    LEFT JOIN listing_images li ON l.listings_id = li.listings_id AND li.is_primary = 1
    WHERE l.listing_owner_id = ?
    ORDER BY l.date_created DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userID]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Listings</title>
      <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">
</head>
<body>
<header><?php include './public/components/header.php'; ?></header>
<main>
  <h2>My Listings</h2>
  <a href="create_listing.php" class="chat-btn">+ Add New Listing</a>
  <?php if ($listings): ?>
    <ul class="listing-list">
      <?php foreach ($listings as $listing): ?>
        <li>
          <img src="<?= htmlspecialchars($listing['image_url'] ?? '/public/assets/images/placeholder.png') ?>" width="80">
          <strong><?= htmlspecialchars($listing['name']) ?></strong> - $<?= $listing['price'] ?><br>
          <a href="edit_listing.php?id=<?= $listing['listings_id'] ?>">Edit</a> |
          <a href="delete_listing.php?id=<?= $listing['listings_id'] ?>" onclick="return confirm('Delete this listing?')">Delete</a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>You haven’t created any listings yet.</p>
  <?php endif; ?>
</main>
<footer><?php include './public/components/footer.php'; ?></footer>
</body>
</html>

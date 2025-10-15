<?php
session_start();
require './includes/db_connection.php';

if (empty($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: index.php");
    exit();
}

$userID = (int)$_SESSION['user_id'];

// Fetch orders where I am the seller
$sql = "
    SELECT o.order_id, o.status, o.date_created,
           l.listings_id, l.name, l.price,
           u.first_name AS buyer_first, u.last_name AS buyer_last,
           li.image_url
    FROM orders o
    INNER JOIN listings l ON o.listings_id = l.listings_id
    INNER JOIN user u ON o.user_id = u.user_id   -- buyer comes from orders.user_id
    LEFT JOIN listing_images li 
           ON l.listings_id = li.listings_id AND li.is_primary = 1
    WHERE l.listing_owner_id = :seller_id        -- seller comes from listings
    ORDER BY o.date_created DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':seller_id' => $userID]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Sales</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">
</head>
<body>
<header><?php include './public/components/header.php'; ?></header>
<main>
  <h2>My Sales</h2>
  <?php if ($sales): ?>
    <ul class="order-list">
      <?php foreach ($sales as $order): ?>
        <li>
          <img src="/public/assets/images/products/<?= htmlspecialchars($order['image_url'] ?? '/public/assets/images/placeholder.png') ?>" width="80">
          <strong><?= htmlspecialchars($order['name']) ?></strong> - $<?= $order['price'] ?><br>
          Buyer: <?= htmlspecialchars($order['buyer_first'].' '.$order['buyer_last']) ?><br>
          Status: <?= htmlspecialchars($order['status']) ?>
          <!-- Example: add buttons to update status -->
          <form method="post" action="update_order_status.php">
            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
            <select name="status">
              <option value="pending" <?= $order['status']==='pending'?'selected':'' ?>>Pending</option>
              <option value="on hold" <?= $order['status']==='on hold'?'selected':'' ?>>On Hold</option>
              <option value="shipped" <?= $order['status']==='shipped'?'selected':'' ?>>Shipped</option>
              <option value="delivered" <?= $order['status']==='delivered'?'selected':'' ?>>Delivered</option>
              <option value="completed" <?= $order['status']==='completed'?'selected':'' ?>>Completed</option>
              <option value="cancelled" <?= $order['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
            <button type="submit">Update</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No sales yet.</p>
  <?php endif; ?>
</main>
<footer><?php include './public/components/footer.php'; ?></footer>
</body>
</html>

<?php
session_start();
require __DIR__ . '/includes/db_connection.php';

if (!isset($_GET['order_id'])) {
    http_response_code(400);
    die("Missing order ID.");
}

$orderId = (int)$_GET['order_id'];

// Fetch order details
$sql = "SELECT 
          o.*, 
          l.name AS listing_name, 
          COALESCE(li.image_url, 'default.jpg') AS image_url, 
          s.street_address, 
          s.barangay, 
          s.city, 
          s.province
        FROM orders o
        JOIN listings l ON o.listings_id = l.listings_id
        LEFT JOIN listing_images li ON l.listings_id = li.listings_id
        JOIN shipping_address s ON o.shipping_address_id = s.shipping_address_id
        WHERE o.order_id = :order_id AND o.user_id = :user_id
        ORDER BY li.is_primary DESC, li.image_id ASC
        LIMIT 1";


$stmt = $pdo->prepare($sql);
$stmt->execute([
    'order_id' => $orderId,
    'user_id' => $_SESSION['user_id']
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    die("Order not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Success | Michaelite Store</title>
  <link rel="stylesheet" href="/public/css/style.css">
  <link rel="stylesheet" href="/public/css/component.css">
  <style>
    
  </style>
</head>
<body>
  <header>
    <?php include './public/components/header.php' ?>
  </header>
  <div class="success-container">
    <div>
      <h1>Order Placed Successfully!</h1>
      <p>Thank you for your purchase. Your order ID is <strong>#<?= $order['order_id'] ?></strong>.</p>
    </div>

    <div class="order-success-actions">
      <a href="/"><button class="chat-btn">Home</button></a>
      <a href=""><button class="chat-btn">My Purchases</button></a>
    </div>
  </div>
<footer>
    <?php include './public/components/footer.php'  ?>
</footer>
</body>
</html>

<?php
session_start();
require './includes/db_connection.php';

if (empty($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: index.php");
    exit();
}

$userID = (int)$_SESSION['user_id'];

$statusFilter = $_GET['status'] ?? 'all';

$whereStatus = '';
$params = [':buyer_id' => $userID];

if ($statusFilter === 'pending') {
    $whereStatus = "AND (o.status = 'pending' OR o.status = 'onhold')";
} elseif ($statusFilter === 'processing') {
    $whereStatus = "AND (o.status = 'processing' OR o.status = 'shipped')";
} elseif ($statusFilter === 'completed') {
    $whereStatus = "AND o.status = 'completed'";
} elseif ($statusFilter === 'cancelled') {
    $whereStatus = "AND o.status = 'cancelled'";
}

$sql = "
    SELECT o.order_id, o.status, o.date_created,
           l.name AS listing_name, l.price,
           u.first_name AS seller_first, u.last_name AS seller_last,
           li.image_url
    FROM orders o
    INNER JOIN listings l ON o.listings_id = l.listings_id
    INNER JOIN user u ON l.listing_owner_id = u.user_id
    LEFT JOIN listing_images li ON l.listings_id = li.listings_id AND li.is_primary = 1
    WHERE o.user_id = :buyer_id
    $whereStatus
    ORDER BY o.date_created DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':buyer_id' => $userID]);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countSql = "
    SELECT 
      SUM(CASE WHEN status IN ('pending','onhold') THEN 1 ELSE 0 END) AS pending_count,
      SUM(CASE WHEN status IN ('processing','shipped') THEN 1 ELSE 0 END) AS processing_count,
      SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
      SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count,
      COUNT(*) AS all_count
    FROM orders
    WHERE user_id = :buyer_id
";
$stmt = $pdo->prepare($countSql);
$stmt->execute([':buyer_id' => $userID]);
$statusCounts = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Purchases</title>
      <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">
</head>
<body>
<header><?php include './public/components/header.php'; ?></header>
<main>
  
</main>

<footer><?php include './public/components/footer.php'; ?></footer>
</body>
</html>

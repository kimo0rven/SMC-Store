<?php
session_start();
require __DIR__ . '/includes/db_connection.php';

if (empty($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: index.php");
    exit();
}

$contentType = $_SERVER["CONTENT_TYPE"] ?? '';
$isJson = strpos($contentType, 'application/json') !== false;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Invalid request method.");
}

if ($isJson) {
    $input = json_decode(file_get_contents("php://input"), true);
} else {
    $input = $_POST;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die("User not authenticated.");
}

$userId            = $_SESSION['user_id'];
$listingId         = (int)($input['listing_id'] ?? 0);
$shippingAddressId = (int)($input['shipping_address_id'] ?? 0);
$deliveryOption    = $input['delivery'] ?? '';
$paymentMethod     = $input['payment'] ?? '';
$orderID           = $input['orderID'] ?? null;

$sql = "SELECT price, stock_quantity FROM listings WHERE listings_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $listingId]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    http_response_code(404);
    die("Listing not found.");
}

$orderAmount = $listing['price'];

$status = ($paymentMethod === 'cod') ? 'completed' : 'pending';

$sql = "INSERT INTO orders
        (user_id, listings_id, shipping_address_id, delivery_option, payment_method, amount, status, date_created) 
        VALUES 
        (:user_id, :listings_id, :shipping_address_id, :delivery_option, :payment_method, :amount, :status, NOW())";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'user_id'            => $userId,
    'listings_id'         => $listingId,
    'shipping_address_id'=> $shippingAddressId,
    'delivery_option'    => $deliveryOption,
    'payment_method'     => $paymentMethod,
    'amount'             => $orderAmount,
    'status'             => $status
]);

$newOrderId = $pdo->lastInsertId();

$updateStockSql = "UPDATE listings
                    SET stock_quantity = stock_quantity - 1,
                    listing_status = CASE 
                                        WHEN stock_quantity - 1 <= 0 THEN 'sold' 
                                        ELSE listing_status 
                                    END
                    WHERE listings_id = :listing_id
                    AND stock_quantity > 0;
                    ";
$updateStockStmt = $pdo->prepare($updateStockSql);
$updateStockStmt->execute(['listing_id' => $listingId]);


if ($paymentMethod === 'cod') {
    header("Location: order_success.php?order_id=" . $newOrderId);
    exit;
}

if ($paymentMethod === 'paypal') {
    echo json_encode([
        'success' => true,
        'order_id' => $newOrderId
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unsupported payment method']);

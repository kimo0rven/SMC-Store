<?php
session_start();
require_once './includes/db_connection.php'; // your PDO connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Accept both form and JSON input
$input = $_SERVER['CONTENT_TYPE'] === 'application/json'
  ? json_decode(file_get_contents("php://input"), true)
  : $_POST;

// Extract data
$userId = $_SESSION['user_id'] ?? null;
$listingId = $input['listing_id'] ?? null;
$delivery = $input['delivery'] ?? null;
$addressId = $input['shipping_address_id'] ?? null;
$paymentMethod = strtolower($input['payment_method'] ?? 'cod');
$paypalOrderId = $input['paypal_order_id'] ?? null;

// Basic validation
if (!$userId || !$listingId || !$delivery || ($delivery !== 'meetup' && !$addressId)) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing required fields']);
  exit;
}

// Determine shipping method label
$shippingMethod = match ($delivery) {
  'ship' => 'Ship to Address',
  'rider' => 'Local Rider Drop',
  'meetup' => 'Meet Up',
  default => 'Unknown'
};

// Determine shipping cost
$shippingCost = ($delivery === 'meetup') ? 0 : 150;

// Get listing price from conversation
$stmt = $pdo->prepare("SELECT current_offer_amount FROM conversation WHERE listings_id = ? AND buyer_id = ?");
$stmt->execute([$listingId, $userId]);
$conversation = $stmt->fetch(PDO::FETCH_ASSOC);
$subtotal = $conversation['current_offer_amount'] ?? 0;
$totalAmount = $subtotal + $shippingCost;

// Insert order
$stmt = $pdo->prepare("
  INSERT INTO orders (
    user_id,
    listings_id,
    order_date,
    total_amount,
    status,
    shipping_address_id,
    delivery_method,
    payment_method,
    paypal_order_id,
    date_created,
    date_modified
  ) VALUES (
    :user_id,
    :listings_id,
    NOW(),
    :total_amount,
    'pending',
    :shipping_address_id,
    :shipping_method,
    :payment_method,
    :paypal_order_id,
    NOW(),
    NOW()
  )
");

$success = $stmt->execute([
  ':user_id' => $userId,
  ':listings_id' => $listingId,
  ':total_amount' => $totalAmount,
  ':shipping_address_id' => $delivery === 'meetup' ? null : $addressId,
  ':shipping_method' => $shippingMethod,
  ':payment_method' => $paymentMethod,
  ':paypal_order_id' => $paypalOrderId ?? null
]);

if ($success) {
  $orderId = $pdo->lastInsertId();

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    header("Location: order_success.php?order_id=$orderId");
    exit;
  }

  echo json_encode(['status' => 'success', 'order_id' => $orderId]);
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to create order']);
}
?>

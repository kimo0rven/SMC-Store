<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$clientId = 'AdKIMo4pfQ_z4QD24wosQfk_TqH8TVXKWgMZ1h77OzcL1fG9_3nMoxhR9uwy6AXzDIhnpyC8RFfuiDkh';
$secret = 'EEbrRiJFIftW79k1sdc6YTcwMEFdD-QAejF-7VUjaQXWvnkEY8SDf81TQmp5UGG3BT-Uq031m7iwXx_l';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$clientId:$secret");
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "Accept-Language: en_US"
]);
$response = curl_exec($ch);
$data = json_decode($response, true);
$accessToken = $data['access_token'] ?? null;

if (!$accessToken) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get access token']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$listingId = $input['listing_id'] ?? 'LISTING_123';
$amount = $input['amount'] ?? '0.0';
$currency = $input['currency'] ?? 'PHP';
$shippingPref = $input['shipping_preference'] ?? 'NO_SHIPPING';
$locale = $input['locale'] ?? 'en-PH';
$name = $input['listingName'] ?? null;
$shippingAddressId = $input['shippingAddressId'] ?? null;
$paymentMode = $input['paymentMode'] ?? null;

$orderData = [
    'status' => 'CREATED',
    'intent' => 'CAPTURE',
    'purchase_units' => [[
        'reference_id' => $listingId,
        'amount' => [
        'currency_code' => $currency,
        'value' => $amount
        ],
        'description' => $name
    ]],
    'application_context' => [
        'shipping_preference' => $shippingPref,
        'locale' => $locale,
        'user_action' => 'PAY_NOW'
    ],
    'create_time' => date('c'),
    'links' => [[
        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=DUMMY-PAYPAL-ORDER-ID-123456',
        'rel' => 'approve',
        'method' => 'GET'
    ]]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v2/checkout/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $accessToken"
    ]);
$response = curl_exec($ch);
$order = json_decode($response, true);

echo json_encode([
    'status' => 'created',
    'orderID' => $order['id'] ?? null
]);




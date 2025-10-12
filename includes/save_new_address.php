<?php
require 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

$street = $data['street'] ?? '';
$barangay = $data['barangay'] ?? '';
$city = $data['city'] ?? '';
$province = $data['province'] ?? '';
$type = $data['type'] ?? '';
$userId = $data['user_id'] ?? null;

if ($userId && $street && $barangay && $city && $province && $type) {
  $stmt = $pdo->prepare("INSERT INTO shipping_addresses (user_id, street_address, barangay, city, province, type) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([$userId, $street, $barangay, $city, $province, $type]);
  echo json_encode(['status' => 'success', 'address_id' => $pdo->lastInsertId()]);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
}

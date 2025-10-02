<?php
session_start();
require './db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to post a listing.");
}

$userId        = $_SESSION['user_id'];
$categoryId    = $_POST['category'] ?? null;
$subcategoryId = $_POST['subcategory'] ?? null;
$title         = trim($_POST['title'] ?? '');
$brand         = trim($_POST['brand'] ?? '');
$condition     = $_POST['condition'] ?? '';
$price         = $_POST['price'] ?? null;
$stock         = $_POST['stock'] ?? null;
$description   = trim($_POST['description'] ?? '');

if (!$categoryId || !$subcategoryId || !$title || !$price || !$stock || !$brand) {
    die("Missing required fields.");
}

print_r($_POST);

$insertListing = "
    INSERT INTO listings (
        listing_owner_id, name, brand, description, price, stock_quantity,
        subcategory_id, listing_status, date_created, date_modified, `item_condition`, discount
    ) VALUES (
        :owner_id, :name, :brand, :description, :price, :stock, :subcategory_id,
        :listing_status, NOW(), NOW(), :item_condition, :discount
    )
";

$stmt = $pdo->prepare($insertListing);
$stmt->execute([
    'owner_id'       => $userId,
    'name'           => $title,
    'brand'          => $brand,
    'description'    => $description,
    'price'          => $price,
    'stock'          => $stock,
    'subcategory_id' => $subcategoryId,
    'listing_status' => 'active',
    'item_condition'      => $condition,
    'discount'       => null
]);

$listingId = $pdo->lastInsertId();

$listingSlug = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($title));
$folderName  = "{$listingId}_{$listingSlug}";
$uploadDir = __DIR__ . "/../public/assets/images/products/{$folderName}/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$images = $_FILES['images'];
echo '<pre>'; print_r($_FILES['images']); echo '</pre>';

for ($i = 0; $i < count($images['name']) && $i < 6; $i++) {
    if ($images['error'][$i] === UPLOAD_ERR_OK) {
        $tmpName      = $images['tmp_name'][$i];
        $originalName = basename($images['name'][$i]);
        $ext          = pathinfo($originalName, PATHINFO_EXTENSION);

        $mimeType = mime_content_type($tmpName);
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) continue;
        if (filesize($tmpName) > 5 * 1024 * 1024) continue;

        $safeName   = "{$listingId}_{$listingSlug}_{$i}." . strtolower($ext);
        $targetPath = $uploadDir . $safeName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $isPrimary = ($i === 0) ? 1 : 0;
            $relativePath = "/{$folderName}/{$safeName}";

            $stmtImg = $pdo->prepare("
                INSERT INTO listing_images (listings_id, image_url, is_primary)
                VALUES (:listing_id, :image_url, :is_primary)
            ");
            $stmtImg->execute([
                'listing_id' => $listingId,
                'image_url'  => $relativePath,
                'is_primary' => $isPrimary
            ]);

            echo "Saving image: $relativePath<br>";
        }
    }
}

header("Location: /product.php?id=" . urlencode($listingId));
exit;

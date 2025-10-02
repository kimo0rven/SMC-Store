<?php 
session_start();
include './includes/db_connection.php';
include 'includes/config.php';

$_SESSION['isLoggedIn'] = $_SESSION['isLoggedIn'] ?? false;

$sql = "
    SELECT 
        l.listings_id,
        l.listing_owner_id,
        l.name,
        l.brand,
        l.description,
        l.price,
        l.stock_quantity,
        l.subcategory_id,
        l.listing_status,
        l.date_created,
        l.date_modified,
        l.item_condition,
        l.discount,
        li.image_url,
        li.is_primary
    FROM listings l
    LEFT JOIN listing_images li 
        ON l.listings_id = li.listings_id
    WHERE l.listing_status = 'active'
";

if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
    $searchQuery = '%' . $_GET['search_query'] . '%';
    $sql .= " AND (l.name LIKE :search_query OR l.brand LIKE :search_query OR l.description LIKE :search_query)";
}

$sql .= " ORDER BY l.listings_id, li.is_primary DESC";
$stmt = $pdo->prepare($sql);
if (!empty($searchQuery)) {
    $stmt->bindParam(':search_query', $searchQuery);
}

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$products = [];
foreach ($rows as $row) {
    $id = $row['listings_id'];

    if (!isset($products[$id])) {
        $products[$id] = [
            'listings_id'       => $row['listings_id'],
            'listing_owner_id'  => $row['listing_owner_id'],
            'name'              => $row['name'],
            'brand'             => $row['brand'],
            'description'       => $row['description'],
            'price'             => $row['price'],
            'stock_quantity'    => $row['stock_quantity'],
            'subcategory_id'    => $row['subcategory_id'],
            'listing_status'    => $row['listing_status'],
            'date_created'      => $row['date_created'],
            'date_modified'     => $row['date_modified'],
            'item_condition'    => $row['item_condition'],
            'discount'          => $row['discount'],
            'images'            => []
        ];
    }

    if (!empty($row['image_url'])) {
        $products[$id]['images'][] = [
            'url'        => $row['image_url'],
            'is_primary' => $row['is_primary']
        ];
    }
}
$products = array_values($products);

$sql = "SELECT * FROM categories";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt -> fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?> | Your Campus, Your Store</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">
</head>
<body>
<header>
    <?php include './public/components/header.php' ?>
    <!-- <div class="category-bar">
        <div class="category-list">
            <div><a href="">Likes</a></div>
            <div><a href="">Fashion</a></div>
            <div><a href="">Electronics</a></div>
            <div class="mobile-hidden"><a href="">Home & Garden</a></div>
            <div class="mobile-hidden"><a href="">Beauty</a></div>
            <div class="mobile-hidden"><a href="">Food</a></div>
            <div class="mobile-hidden"><a href="">Media</a></div>
            <div class="mobile-hidden"><a href="">Toys & Hobbies</a></div>
            <div class="categ-dropdown">
                <div onclick="categ_myFunction()" class="categ-dropbtn">All Categories</div>
                <div id="categ-myDropdown" class="categ-dropdown-content">
                    <a href="#">Link 1</a>
                    <a href="#">Link 2</a>
                    <a href="#">Link 3</a>
                </div>
            </div>
        </div>
    </div> -->
</header>

<main style="flex-direction:column">
    <div class="products-categories">
        <?php foreach ($categories as $category): ?>
        <div class="products-category">
            <img src="/public/assets/images/product1.jpg" alt="">
            <p><?= htmlspecialchars($category['category_name']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="products-wrapper">
        <div class="products-display">
            <?php foreach ($products as $listing): ?>
                <?php include './public/components/listing_card.php'  ?>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<footer>
    <?php include './public/components/footer.php'  ?>
</footer>

</body>
</html>


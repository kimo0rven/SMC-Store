<?php 
session_start();
require_once './includes/db_connection.php';
require_once 'includes/config.php';

$_SESSION['isLoggedIn'] = $_SESSION['isLoggedIn'] ?? false;
$title = "Home";

$searchQuery = null;
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
</header>

<main style="flex-direction:column; justify-content: center; align-items:center; gap: 0">
        <div class="products-categories">
            <?php foreach ($categories as $category): ?>
                <a href="/search.php?category=<?= htmlspecialchars($category['category_id']) ?>" class="products-category">
                <div class="category-image">
                    <img src="/public/assets/images/icons/categories/<?= htmlspecialchars($category['category_image']) ?>" alt="<?= htmlspecialchars($category['category_name']) ?>">
                </div>
    <p><?= htmlspecialchars($category['category_name']) ?></p>
</a>

            <?php endforeach; ?>
        </div>

        <section class="home-slogan">
            <h2 class="home-slogan-heading">
                Sell and Buy. Your Campus, Your Store
            </h2>
            <div class="home-slogan-points">
                <div class="home-slogan-point">
                <img class="home-slogan-icon" src="/public/assets/images/icons/homepage/123.png" alt="">
                <p class="home-slogan-text">1 in 2 students find what they need on campus</p>
                </div>

                <div class="home-slogan-point">
                <img class="home-slogan-icon" src="/public/assets/images/icons/homepage/123123.png" alt="">
                <p class="home-slogan-text">Turn your extra stuff into extra cash</p>
                </div>

                <div class="home-slogan-point">
                <img class="home-slogan-icon" src="/public/assets/images/icons/homepage/123123123.png" alt="">
                <p class="home-slogan-text">Buy unique finds at a fraction of the cost</p>
                </div>
            </div>
        </section>

        <section style="padding:0" class="home-slogan">
            <div id="homepage-recommended-for-you">
                <h2 style="margin:0" class="home-slogan-heading">
                    Recommended For You
                </h2>
            </div>
        </section>
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


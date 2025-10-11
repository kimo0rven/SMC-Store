<?php
session_start();
include './includes/db_connection.php';
include 'includes/config.php';

// --- Active filters from query string ---
$activeCategoryId    = $_GET['category']    ?? null;
$activeSubcategoryId = $_GET['subcategory'] ?? null;
$searchQuery         = $_GET['search_query'] ?? null;
$sort                = $_GET['sort'] ?? '';
$condition           = $_GET['condition'] ?? '';
$priceMin            = $_GET['price_min'] ?? '';
$priceMax            = $_GET['price_max'] ?? '';

// --- Fetch categories + subcategories ---
$sqlCategories = "SELECT c.category_id,
                         c.category_name,
                         s.subcategory_id,
                         s.subcategory_name
                  FROM categories c
                  LEFT JOIN subcategories s 
                         ON c.category_id = s.category_id
                  ORDER BY c.category_id, s.subcategory_name";

$stmt = $pdo->prepare($sqlCategories);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build nested categories array
$categories = [];
foreach ($rows as $row) {
    $catId = $row['category_id'];
    if (!isset($categories[$catId])) {
        $categories[$catId] = [
            'id'            => $catId,
            'name'          => $row['category_name'],
            'subcategories' => []
        ];
    }
    if (!empty($row['subcategory_id'])) {
        $categories[$catId]['subcategories'][] = [
            'id'   => $row['subcategory_id'],
            'name' => $row['subcategory_name']
        ];
    }
}
$categories = array_values($categories);

// --- Build products query ---
$sqlProducts = "SELECT l.listings_id,
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
                WHERE 1=1";

$params = [];

// Search filter
if (!empty($searchQuery)) {
    $sqlProducts .= " AND (l.name LIKE :search_query 
                        OR l.brand LIKE :search_query 
                        OR l.description LIKE :search_query)";
    $params[':search_query'] = "%$searchQuery%";
}

// Category filter (all subcategories under category)
if (!empty($activeCategoryId)) {
    $sqlProducts .= " AND l.subcategory_id IN (
                        SELECT subcategory_id 
                        FROM subcategories 
                        WHERE category_id = :category_id
                      )";
    $params[':category_id'] = $activeCategoryId;
}

// Subcategory filter
if (!empty($activeSubcategoryId)) {
    $sqlProducts .= " AND l.subcategory_id = :subcategory_id";
    $params[':subcategory_id'] = $activeSubcategoryId;
}

// Condition filter
if (!empty($condition)) {
    $sqlProducts .= " AND l.item_condition = :condition";
    $params[':condition'] = $condition;
}

// Sorting
switch ($sort) {
    case 'recent':
        $sqlProducts .= " ORDER BY l.date_created DESC";
        break;
    case 'price_high':
        $sqlProducts .= " ORDER BY l.price DESC";
        break;
    case 'price_low':
        $sqlProducts .= " ORDER BY l.price ASC";
        break;
    default:
        $sqlProducts .= " ORDER BY l.listings_id, li.is_primary DESC";
}

$stmt = $pdo->prepare($sqlProducts);
$stmt->execute($params);
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search | Michaelite Stores</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">

</head>
<body style="gap:2rem">
<header>
    <?php include './public/components/header.php' ?>
</header>
<main>
    <div class="search-container">
        <div class="search-sidebar">
            <?php foreach ($categories as $cat): ?>
                <?php 
                    $isActiveCategory = ($activeCategoryId == $cat['id']) 
                                        || ($activeSubcategoryId && in_array($activeSubcategoryId, array_column($cat['subcategories'], 'id')));
                ?>
                <div class="search-category <?= $isActiveCategory ? 'open' : '' ?>">
                    <button class="search-category-toggle <?= $activeCategoryId == $cat['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </button>
                    <ul class="search-subcategory-list">
                        <?php foreach ($cat['subcategories'] as $sub): ?>
                            <li>
                                <a href="/search.php?subcategory=<?= urlencode($sub['id']) ?>"
                                class="<?= $activeSubcategoryId == $sub['id'] ? 'active' : '' ?>">
                                    <?= htmlspecialchars($sub['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="search-right-side">
            <div class="search-filter-div">
                <form method="get" class="search-filters">
                    <?php if ($activeCategoryId): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($activeCategoryId) ?>">
                    <?php endif; ?>
                    <?php if ($activeSubcategoryId): ?>
                        <input type="hidden" name="subcategory" value="<?= htmlspecialchars($activeSubcategoryId) ?>">
                    <?php endif; ?>

                    <label>
                        Sort by:
                        <select name="sort">
                            <option value="">Default</option>
                            <option value="recent" <?= ($sort === 'recent') ? 'selected' : '' ?>>Most Recent</option>
                            <option value="price_high" <?= ($sort === 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="price_low" <?= ($sort === 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
                        </select>
                    </label>

                    <label>
                        Condition:
                        <select name="condition">
                            <option value="">All</option>
                            <option value="brand new"     <?= ($condition === 'brand new') ? 'selected' : '' ?>>Brand New</option>
                            <option value="like new"      <?= ($condition === 'like new') ? 'selected' : '' ?>>Like New</option>
                            <option value="lightly used"  <?= ($condition === 'lightly used') ? 'selected' : '' ?>>Lightly Used</option>
                            <option value="well used"     <?= ($condition === 'well used') ? 'selected' : '' ?>>Well Used</option>
                            <option value="heavily used"  <?= ($condition === 'heavily used') ? 'selected' : '' ?>>Heavily Used</option>
                            <option value="refurbished"   <?= ($condition === 'refurbished') ? 'selected' : '' ?>>Refurbished</option>
                        </select>
                    </label>
                    <button class="chat-btn" type="submit">Apply</button>
                </form>
            </div>

            <div class="products-wrapper">
            <div class="products-display">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $listing): ?>
                        <?php include './public/components/listing_card.php'  ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </div>
        </div>
        </div>

    </div>
</main>
<footer>
    <?php include './public/components/footer.php'  ?>
</footer>

<script>
    document.querySelectorAll('.search-category-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.parentElement.classList.toggle('open');
    });
    });
</script>

</body>
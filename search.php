<?php
session_start();
include "./includes/db_connection.php";
include "includes/config.php";

$sql = "SHOW COLUMNS FROM listings LIKE 'item_condition'";
$stmt = $pdo->query($sql);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$type = $row["Type"];
preg_match("/^enum\('(.*)'\)$/", $type, $matches);
$itemCondition = explode("','", $matches[1]);

$activeCategoryId = $_GET["category"] ?? null;
$activeSubcategoryId = $_GET["subcategory"] ?? null;
$searchQuery = $_GET["search_query"] ?? null;
$sort = $_GET["sort"] ?? "";
$condition = $_GET["condition"] ?? "";
$priceMin = $_GET["price_min"] ?? "";
$priceMax = $_GET["price_max"] ?? "";

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

$categories = [];
foreach ($rows as $row) {
    $catId = $row["category_id"];
    if (!isset($categories[$catId])) {
        $categories[$catId] = [
            "id" => $catId,
            "name" => $row["category_name"],
            "subcategories" => [],
        ];
    }
    if (!empty($row["subcategory_id"])) {
        $categories[$catId]["subcategories"][] = [
            "id" => $row["subcategory_id"],
            "name" => $row["subcategory_name"],
        ];
    }
}
$categories = array_values($categories);

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

if (!empty($searchQuery)) {
    $sqlProducts .= " AND (l.name LIKE :search_query 
                        OR l.brand LIKE :search_query 
                        OR l.description LIKE :search_query)";
    $params[":search_query"] = "%$searchQuery%";
}

if (!empty($activeCategoryId)) {
    $sqlProducts .= " AND l.subcategory_id IN (
                        SELECT subcategory_id 
                        FROM subcategories 
                        WHERE category_id = :category_id
                        )";
    $params[":category_id"] = $activeCategoryId;
}

if (!empty($activeSubcategoryId)) {
    $sqlProducts .= " AND l.subcategory_id = :subcategory_id";
    $params[":subcategory_id"] = $activeSubcategoryId;
}

if (!empty($condition)) {
    $sqlProducts .= " AND l.item_condition = :condition";
    $params[":condition"] = $condition;
}

switch ($sort) {
    case "recent":
        $sqlProducts .= " ORDER BY l.date_created DESC";
        break;
    case "price_high":
        $sqlProducts .= " ORDER BY l.price DESC";
        break;
    case "price_low":
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
    $id = $row["listings_id"];
    if (!isset($products[$id])) {
        $products[$id] = [
            "listings_id" => $row["listings_id"],
            "listing_owner_id" => $row["listing_owner_id"],
            "name" => $row["name"],
            "brand" => $row["brand"],
            "description" => $row["description"],
            "price" => $row["price"],
            "stock_quantity" => $row["stock_quantity"],
            "subcategory_id" => $row["subcategory_id"],
            "listing_status" => $row["listing_status"],
            "date_created" => $row["date_created"],
            "date_modified" => $row["date_modified"],
            "item_condition" => $row["item_condition"],
            "discount" => $row["discount"],
            "images" => [],
        ];
    }
    if (!empty($row["image_url"])) {
        $products[$id]["images"][] = [
            "url" => $row["image_url"],
            "is_primary" => $row["is_primary"],
        ];
    }
}
$products = array_values($products);

if (!empty($searchQuery)) {
    $logSql = "INSERT INTO search_logs (keyword, created_at) 
            VALUES (:keyword, NOW())";
    $logStmt = $pdo->prepare($logSql);
    $logStmt->execute([
        ':keyword' => $_GET['search_query']
    ]);
}

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
			<?php include "./public/components/header.php"; ?>
		</header>
		<main>
			<div class="search-container">
				<div class="search-right-side">
					<div class="search-filter-div">
						<form method="get" class="search-filters">
                            <?php if (!empty($searchQuery)): ?>
                                <input type="hidden" name="search_query" value="<?= htmlspecialchars($searchQuery) ?>">
                            <?php endif; ?>

                            <div class="search-form-filter">
                                <label>
                                    Sort by:
                                    <select name="sort" id="sort">
                                        <option value="">Default</option>
                                        <option value="recent" <?= $sort === "recent" ? "selected" : "" ?>>Most Recent</option>
                                        <option value="price_low" <?= $sort === "price_low" ? "selected" : "" ?>>Price: Low to High</option>
                                        <option value="price_high" <?= $sort === "price_high" ? "selected" : "" ?>>Price: High to Low</option>
                                    </select>
                                </label>
                            </div>

                            <div class="search-form-filter">
                                <label>
                                    Condition:
                                    <select name="condition" id="condition">
                                        <option value="">All Conditions</option>
                                        <?php foreach ($itemCondition as $cond): ?>
                                            <option value="<?= htmlspecialchars($cond) ?>"
                                                <?= $condition === $cond ? "selected" : "" ?>>
                                                <?= htmlspecialchars($cond) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>

                            <div class="search-form-filter">
                                <label>
                                    Category:
                                    <select name="category" id="category-select">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat["id"]) ?>"
                                                <?= $activeCategoryId == $cat["id"] ? "selected" : "" ?>>
                                                <?= htmlspecialchars($cat["name"]) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>

                            <div class="search-form-filter">
                                <label>
                                    Subcategory:
                                    <select name="subcategory" id="subcategory-select">
                                        <option value="">All Subcategories</option>
                                    </select>
                                </label>
                            </div>

                            <button class="chat-btn">Apply Filter</button>
                        </form>

					</div>
					<div class="search-wrapper">
                        <div class="search-display">
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $listing): ?>
                                    <?php include "./public/components/listing_card.php"; ?>
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
			<?php include "./public/components/footer.php"; ?>
		</footer>
		<script>
			const categoriesData = <?= json_encode($categories) ?>;
			
			document.addEventListener("DOMContentLoaded", () => {
                const form = document.querySelector(".search-filters");
                const categorySelect = document.getElementById("category-select");
                const subcategorySelect = document.getElementById("subcategory-select");
			
                function populateSubcategories(catId) {
                    subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
                    
                    if (!catId || !categoriesData) return;
                    
                    const category = categoriesData.find(c => c.id == catId);
                    if (category && category.subcategories.length > 0) {
                        category.subcategories.forEach(sub => {
                            const opt = document.createElement("option");
                            opt.value = sub.id;
                            opt.textContent = sub.name;
                            
                            if ("<?= $activeSubcategoryId ?>" == sub.id) {
                                opt.selected = true;
                            }
                            subcategorySelect.appendChild(opt);
                        });
                    }
                }
                
                populateSubcategories(categorySelect.value);
                
                categorySelect.addEventListener("change", () => {
                    subcategorySelect.value = "";
                    form.submit();
                });
                
                form.querySelectorAll("select").forEach(select => {
                    select.addEventListener("change", () => {
                        if (select.id !== "category-select") {
                            form.submit();
                        }
                    });
                });
			});
		</script>
	</body>
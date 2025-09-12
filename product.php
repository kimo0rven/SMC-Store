<?php
session_start();
require './includes/db_connection.php';

if (isset($_GET['id'])) {
    $sql = "
        SELECT l.*, u.*
        FROM listings AS l
        INNER JOIN user AS u ON l.listing_owner_id = u.user_id
        WHERE l.listings_id = :id
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $_GET['id']]);

} elseif (isset($_GET['slug'])) {
    $sql = "
        SELECT l.*, u.*
        FROM listings AS l
        INNER JOIN user AS u ON l.listing_owner_id = u.user_id
        WHERE l.slug = :slug
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['slug' => $_GET['slug']]);

} else {
    die("No product specified.");
}

$product = $stmt->fetch(PDO::FETCH_ASSOC);

// print_r($product);

if (!$product) {
    die("Product not found.");
}

$productImg = '/products/'.$product['image_url'];

$images = [
    ["src" => $productImg, "alt" => "Pink shorts front view"],
    ["src" => "product1.jpg", "alt" => "White shorts"],
    ["src" => "product2.jpg", "alt" => "Black shorts"],
    ["src" => "product3.jpg", "alt" => "Pink shorts rear view"],
    ["src" => "product4.jpg", "alt" => "Orange shorts"],
    ["src" => "product5.jpg", "alt" => "Orange shorts"]
];
$total = count($images);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) . " | Michaelite Store" ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">

    <style>
        
    </style>
</head>
<body>
<header>
    <?php include './public/components/header.php' ?>
</header>

<main>
    <div class="product-view-wrapper">

    
    <div class="product-view-container">
        
        <div class="product-view-left">
            <div class="product-view-gallery">
                <?php foreach ($images as $i => $img) { ?>
                    
                        <img src="./public/assets/images/<?= htmlspecialchars($img['src']) ?>" 
                        <?= htmlspecialchars($img['alt']) ?>">
                    
                <?php } ?>
            </div>

            <div class="product-view-description">
                <p class="product-view-description-header">Description</p>
                <p class="product-view-description-display"><?= htmlspecialchars($product['description']) ?></p>
            </div>
        </div>

        <div class="product-view-right">
            <div class="product-view-seller-information">
                <div class="product-view-seller-information-left"><img src="./public/assets/images/product1.jpg" alt=""></div>
                <div class="product-view-seller-information-right">
                    <div class="product-view-seller-name">
                        <a href="/user.php"><?= htmlspecialchars($product['first_name'] . " " . $product['last_name']) ?></a>
                    </div>

                    <div class="product-view-seller-other">
                        <span>100% positive</span>
                        <span>Contact Seller</span>
                    </div>
                </div>
            </div>

            <div class="product-view-name">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-view-other-actions">
                    <span><img src="./public/assets/images/icons/share_icon.png" alt=""></span>
                    <span><img src="./public/assets/images/icons/bookmark_icon.png" alt=""></span>
                </div>
            </div>

            <div class="product-view-price">
                <?php if (!empty($product['discount']) && $product['discount'] > 0): ?>
                    <?php
                        
                        $discountedPrice = $product['price'] - ($product['price'] * ($product['discount'] / 100));
                    ?>
                    <span style="text-decoration: line-through; color: #555;">
                        <?= "PHP " . htmlspecialchars(number_format($product['price'], 2)) ?>
                    </span>
                    <span style="color: red; font-weight: bold;">
                        <?= "PHP " . htmlspecialchars(number_format($discountedPrice, 2)) ?>
                    </span>
                    <span style="color: red; font-size: 0.6em;">
                        Sale
                    </span>
                <?php else: ?>
                    <span style="color: black; font-weight: bold;"></span>
                        <?= "PHP " . htmlspecialchars(number_format($product['price'], 2)) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="product-view-condition">
                <span>Condition: </span><span class="product-view-condition-display"><?= htmlspecialchars($product['condition']) ?></span>
            </div>
            
            <div class="product-view-action-buttons-container">
                <button>Chat</button>

                <div class="price-offer">
                    <span class="price">PHP</span>
                        <input type="number" id="price-offer-field" name="" value="112<?=number_format($product['price'], 2) ?>">
                    
                    <button class="make-offer-btn">Make Offer</button>
                </div>

            </div>
        </div>
    </div>

    <div class="product-view-recently-viewed">
        <p class="product-view-recently-viewed-header">Recently Viewed</p>
    </div>

    
</main>


<footer>
    <?php include './public/components/footer.php'  ?>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const radios = Array.from(document.querySelectorAll('[name="product-view-slides"]'));
    const slides = Array.from(document.querySelectorAll('.product-view-main .product-view-slide'));
    const allNavs = Array.from(document.querySelectorAll('.product-view-main .product-view-nav'));
    const thumbsWrapper = document.querySelector('.product-view-thumbs-wrapper');
    const thumbsContainer = document.querySelector('.product-view-thumbs');
    const thumbs = Array.from(document.querySelectorAll('.product-view-thumbs label'));

    function getActiveIndex() {
        return Math.max(0, radios.findIndex(r => r.checked));
    }

    function updateSlides(activeIndex) {
        slides.forEach((s, i) => s.classList.toggle('is-active', i === activeIndex));
    }

    function updateNavVisibility(activeIndex) {
        allNavs.forEach(n => n.style.display = 'none');
        const pair = document.querySelectorAll(`.product-view-main .product-view-nav.product-view-s${activeIndex + 1}`);
        pair.forEach(n => n.style.display = 'block');
    }

    function updateThumbsShift(activeIndex) {
        if (!thumbsContainer || !thumbsWrapper || thumbs.length === 0) return;

        const thumbWidth = thumbs[0].offsetWidth;
        const gap = parseFloat(getComputedStyle(thumbsContainer).gap) || 0;
        const wrapperWidth = thumbsWrapper.clientWidth;

        const perRow = Math.max(1, Math.floor((wrapperWidth + gap) / (thumbWidth + gap)));

        const group = Math.floor(activeIndex / perRow);
        const shift = group * ((thumbWidth + gap) * perRow);

        thumbsContainer.style.transform = `translateX(-${shift}px)`;
    }

    function applyAll() {
        const idx = getActiveIndex();
        updateSlides(idx);
        updateNavVisibility(idx);
        updateThumbsShift(idx);
    }

    radios.forEach((r, i) => r.addEventListener('change', applyAll));

    applyAll();
    window.addEventListener('resize', () => {

        updateThumbsShift(getActiveIndex());
    });
    });

    document.getElementById('share-btn').addEventListener('click', function() {

    navigator.clipboard.writeText(window.location.href)
        .then(() => {
        const msg = document.getElementById('copy-msg');
        msg.style.display = 'block';

        setTimeout(() => {
            msg.style.display = 'none';
        }, 2000);
        })
        .catch(err => {
        console.error('Failed to copy: ', err);
        });
    });
</script>

</body>
</html>

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

print_r($product);

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
    <div class="product-view-container">
        <ul class="breadcrumb">
        <li><a href="#">Home</a></li>
        <li><a href="#">Fasion</a></li>
        <li><a href="#">Hat</a></li>
        <li>Italy</li>
        </ul>

        <div class="product-view-wrapper">
            <div class="product-view-carousel">

                <?php foreach ($images as $i => $img): ?>
                    <input style="display: none;" type="radio" 
                        name="product-view-slides" 
                        id="product-view-slide<?= $i+1 ?>" 
                        <?= $i === 0 ? 'checked' : '' ?>>
                <?php endforeach; ?>

                <div class="product-view-main">
                    <?php foreach ($images as $i => $img): ?>
                        <div class="product-view-slide" id="product-view-s<?= $i+1 ?>">
                            <img src="./public/assets/images/<?= htmlspecialchars($img['src']) ?>" alt="<?= htmlspecialchars($img['alt']) ?>">
                        </div>
                    <?php endforeach; ?>

                    <?php foreach ($images as $i => $img): 
                        $prev = ($i === 0) ? $total : $i;
                        $next = ($i + 2 > $total) ? 1 : $i + 2;
                    ?>
                        <label for="product-view-slide<?= $prev ?>" 
                            class="product-view-nav product-view-prev product-view-s<?= $i+1 ?>">
                            &#10094;
                        </label>
                        <label for="product-view-slide<?= $next ?>" 
                            class="product-view-nav product-view-next product-view-s<?= $i+1 ?>">
                            &#10095;
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="product-view-thumbs-wrapper">
                    <div class="product-view-thumbs">
                        <?php foreach ($images as $i => $img): ?>
                            <label for="product-view-slide<?= $i+1 ?>">
                                <img src="./public/assets/images/<?= htmlspecialchars($img['src']) ?>" alt="">
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <div class="product-view-details">
                <div class="product-view-seller-information">
                    <img class="product-seller-image" src="./public/assets/images/temp_logo.png" alt="">
                    <div class="product-view-seller-more-info">
                        <p><?= htmlspecialchars($product['first_name'] . " " . $product['last_name']) ?></p>
                        <div><a href="/">100% positive</a>(1 review)</div>
                    </div>
                </div>
                <div class="product-view-name"><?= htmlspecialchars( $product['name']) ?></div>

                <form class="price-offer" action="">
                    <div class="price-input">
                        <span class="currency">PHP</span>
                        <input class="make-offer-input" type="number" name="offer_price" min="1" value="<?= htmlspecialchars($product['price'])?>" placeholder="0">
                    </div>
                    <button class="make-offer-btn">Make Offer</button>
                </form>
                
                <div><button>Add to Bookmarks</button></div>
                <div id="share-btn" style="display: flex; align-items:center; gap:1rem; cursor: pointer;">
                <img src="./public/assets/images/icons/share_icon.png" alt="" style="width:20px; height:20px;">
                <span>Share</span>
                </div>

                <div id="copy-msg" style="display:none; color:green; margin-top:0.5rem;">
                Link copied!
                </div>
            </div>
        </div>

        <div class="product-view-bottom-details">
            <h2>Additional Information</h2>
            
            <div>Condition <br>
                <?= htmlspecialchars( $product['condition']) ?>
            </div>
                            <br>
            <div>
                <?= htmlspecialchars( $product['description']) ?>
            </div>
        </div>
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

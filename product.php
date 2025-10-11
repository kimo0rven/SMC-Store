<?php
session_start();
require './includes/db_connection.php';

if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die("No valid product specified.");
}

$productId = (int) $_GET['id'];

$productQuery = "
    SELECT 
        l.*, 
        u.*, 
        s.subcategory_name, 
        c.category_name,
        l.date_created AS user_date_created,
        li.image_url,
        li.is_primary
    FROM listings AS l
    INNER JOIN `user` AS u 
        ON l.listing_owner_id = u.user_id
    INNER JOIN subcategories AS s 
        ON l.subcategory_id = s.subcategory_id
    INNER JOIN categories AS c 
        ON s.category_id = c.category_id
    LEFT JOIN listing_images AS li
        ON l.listings_id = li.listings_id
    WHERE l.listings_id = :id
    ORDER BY li.is_primary DESC, li.image_id ASC
";

$stmt = $pdo->prepare($productQuery);
$stmt->execute(['id' => $productId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    die("Product not found.");
}

$product = $rows[0];
$product['images'] = [];

foreach ($rows as $row) {
    if (!empty($row['image_url'])) {
        $product['images'][] = [
            'url'        => $row['image_url'],
            'is_primary' => $row['is_primary']
        ];
    }
}

$reviewQuery = "
    SELECT 
        r.*, 
        u.first_name,
        u.last_name, 
        l.name AS listing_name,
        r.date_created as review_datetime
    FROM review AS r
    INNER JOIN user AS u 
        ON r.seller_id = u.user_id
    INNER JOIN listings AS l 
        ON r.listing_id = l.listings_id
    WHERE r.seller_id = :seller_id
";
$stmt2 = $pdo->prepare($reviewQuery);
$stmt2->execute(['seller_id' => $product['listing_owner_id']]);
$reviews = $stmt2->fetchAll(PDO::FETCH_ASSOC) ?: [];

$averagePercentage = 0;
$ratingLabel = 'No reviews yet';

if (!empty($reviews)) {
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $averageRating = $totalRating / count($reviews);
    $averagePercentage = ($averageRating / count($reviews)) * 100; 

    if ($averagePercentage >= 70) {
        $ratingLabel = 'Positive';
    } elseif ($averagePercentage >= 40) {
        $ratingLabel = 'Neutral';
    } else {
        $ratingLabel = 'Negative';
    }
}

$listingCountQuery = "
    SELECT COUNT(*) 
    FROM listings
    WHERE listing_owner_id = :listing_owner_id
    AND `listing_status` = 'sold'
";
$stmt3 = $pdo->prepare($listingCountQuery);
$stmt3->execute(['listing_owner_id' => $product['listing_owner_id']]);
$listingCount = $stmt3->fetchColumn();

$productImg = '/products/' . $product['image_url'];
$images = [
    ["src" => $productImg, "alt" => "Pink shorts front view"],
    ["src" => "product1.jpg", "alt" => "White shorts"],
    ["src" => "product2.jpg", "alt" => "Black shorts"],
    ["src" => "product3.jpg", "alt" => "Pink shorts rear view"],
    ["src" => "product4.jpg", "alt" => "Orange shorts"],
    ["src" => "product5.jpg", "alt" => "Orange shorts"]
];
$totalImages = count($images);

$price = $product['price'];
if (!empty($product['discount']) && $product['discount'] > 0) {
    $price -= $price * ($product['discount'] / 100);
}
$price = number_format($price, 2);

$stmt = $pdo->prepare("
    SELECT l.*, 
        CASE WHEN b.bookmark_id IS NOT NULL THEN 1 ELSE 0 END AS is_bookmarked
    FROM listings l
    LEFT JOIN bookmark b 
        ON b.listings_id = l.listings_id 
        AND b.user_id = :current_user_id
    WHERE l.listings_id = :listings_id
");
$stmt->execute([
    ':current_user_id' => $_SESSION['user_id'] ?? 0,
    ':listings_id'     => $_GET['id'] ?? 0
]);
$product1 = $stmt->fetch(PDO::FETCH_ASSOC);

$isBookmarked = !empty($product1['is_bookmarked']);

try {
    $sql = "INSERT INTO recently_viewed (user_id, listings_id) VALUES (:user_id, :listings_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':listings_id', $product1['listings_id'], PDO::PARAM_INT);
    $stmt->execute();

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

function fetchListingsWithImages(PDO $pdo, string $sql, array $params = []): array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $listings = [];
    foreach ($rows as $row) {
        $id = $row['listings_id'];
        if (!isset($listings[$id])) {
            $listings[$id] = [
                'listings_id'    => $row['listings_id'],
                'name'           => $row['name'],
                'brand'          => $row['brand'],
                'price'          => $row['price'],
                'description'    => $row['description'],
                'item_condition' => $row['item_condition'] ?? null,
                'date_created'   => $row['date_created'] ?? null,
                'images'         => []
            ];
        }
        if (!empty($row['image_url'])) {
            $listings[$id]['images'][] = [
                'url'        => $row['image_url'],
                'is_primary' => $row['is_primary']
            ];
        }
    }
    return $listings;
}

$recentlyVieweds = [];
$stmt = $pdo->prepare("
    SELECT listings_id 
    FROM recently_viewed 
    WHERE user_id = :user_id 
    ORDER BY viewed_at DESC
");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$recentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

$recentlyVieweds = [];
if ($recentIds) {
    $ph = implode(',', array_fill(0, count($recentIds), '?'));
    $recentSql = "
        SELECT l.listings_id, l.name, l.brand, l.price, l.description, l.item_condition, li.image_url, li.is_primary, li.date_created
        FROM listings l
        LEFT JOIN listing_images li ON l.listings_id = li.listings_id
        WHERE l.listings_id IN ($ph)
        ORDER BY FIELD(l.listings_id, $ph)
    ";
    $recentlyVieweds = fetchListingsWithImages($pdo, $recentSql, array_merge($recentIds, $recentIds));
}

$similarSql = "
    SELECT l.listings_id, l.name, l.brand, l.price, l.description, l.item_condition, l.date_created, li.image_url, li.is_primary
    FROM listings l
    LEFT JOIN listing_images li ON l.listings_id = li.listings_id
    WHERE l.subcategory_id IN (
        SELECT subcategory_id 
        FROM subcategories 
        WHERE category_id = (
            SELECT category_id FROM subcategories WHERE subcategory_id = :sub_id
        )
    )
    AND l.listings_id != :current_id
    ORDER BY l.date_created DESC
";
$similarListings = fetchListingsWithImages($pdo, $similarSql, [
    'sub_id'     => $product['subcategory_id'],
    'current_id' => $productId
]);
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
<body style="gap:2rem">
<header>
    <?php include './public/components/header.php' ?>
</header>

<main>
    <div class="product-view-wrapper">
    <span id="test" style="display: none" type="text" name="" ><?= htmlspecialchars(json_encode($reviews), ENT_QUOTES, 'UTF-8') ?></span>
    <span id="listingCount" style="display: none" type="text"><?php echo htmlspecialchars($listingCount); ?></span>
    <div class="product-view-container">
        <div class="product-view-left">
            <div class="product-view-gallery">
                <?php foreach ($product['images'] as $img): ?>
                    <img src="/public/assets/images/products/<?= htmlspecialchars($img['url']) ?>" 
                        alt="<?= htmlspecialchars($product['name']) ?>">
                <?php endforeach; ?>

            </div>
            <div id="lightbox" class="lightbox">
                <span class="lightbox-close">&times;</span>
                <img class="lightbox-image" src="" alt="">
                <span class="lightbox-prev">&#10094;</span>
                <span class="lightbox-next">&#10095;</span>

                <div class="lightbox-thumbnails"></div>
            </div>

            <div class="product-view-description">
                <p class="product-view-description-header">Description</p>
                <p class="product-view-description-display"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            
        </div>
        <div class="product-view-right">
            <div id="sellerInfo" class="product-view-seller-information" data-user-id="<?= htmlspecialchars($product['user_id']) ?>">
                <div class="product-view-seller-information-wrapper">
                    <div class="product-view-seller-information-left">
                        <img src="./public/assets/images/avatars/<?= htmlspecialchars($product['avatar']) ?>" alt="<?= htmlspecialchars($product['first_name'] . " " . $product['last_name']) ?>">
                    </div>
                    <div class="product-view-seller-information-right">
                        <div class="product-view-seller-name">
                        <a href="/user.php"><?= htmlspecialchars($product['first_name'] . " " . $product['last_name']) ?></a>
                        </div>

                        <div style="letter-spacing: 1px" class="product-view-seller-other">
                            <span id="seller-rating">
                                <?php if (!empty($reviews)): ?>
                                    <?= round($averagePercentage, 2) ?>% <?= htmlspecialchars($ratingLabel) ?> Rating
                                <?php else: ?>
                                    <?= htmlspecialchars($ratingLabel) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="product-view-seller-information-modal">&#10095;</div>

            </div>
            
            <?php include './public/components/seller_modal.php' ?>

            <div class="product-view-name">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-view-other-actions">
                    <span id='share-btn'><img src="./public/assets/images/icons/share_icon.png" alt="Share"></span>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['listing_owner_id']) { ?>
                        <span
                            id="product-view-bookmark-btn"
                            data-listings-id="<?= (int)$product['listings_id'] ?>"
                            data-user-id="<?= (int)$_SESSION['user_id'] ?>"
                            data-bookmarked="<?= $isBookmarked ? 1 : 0 ?>">
                            <img src="/public/assets/images/icons/<?= $isBookmarked ? 'bookmark_added_icon.png' : 'bookmark_icon.png' ?>" alt="Bookmark">
                        </span>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <div class="product-view-price">
                <?php if (!empty($product['discount']) && $product['discount'] > 0): ?>
                    <?php
                        
                        $discountedPrice = $product['price'] - ($product['price'] * ($product['discount'] / 100));
                    ?>
                    <span style="text-decoration: line-through; color: #000000;">
                        <?= "PHP " . htmlspecialchars(number_format($product['price'], 2)) ?>
                    </span>
                    <div style="display: flex; flex-direction: row; gap: .5rem">
                        <span style="color: red;">
                        <?= "PHP " . htmlspecialchars(number_format($discountedPrice, 2)) ?>
                        </span>

                        <div>
                            <span style="display: flex; font-size: 1rem"> -10%</span>
                        </div>
                    </div>
                    <span style="color: red; font-size: 0.6em; font-weight: 300">
                        Sale
                    </span>
                <?php else: ?>
                    <span style="color: black; font-weight: bold;">
                        <?= "PHP " . htmlspecialchars(number_format($product['price'], 2)) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="product-view-condition">
                <span>Condition: </span><span class="product-view-condition-display"><?= htmlspecialchars($product['item_condition']) ?></span>
            </div>
            
            <div class="product-view-action-buttons-container">
                <?php 
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['listing_owner_id']) {
                    if ($product['listing_status'] === 'sold' || $product['listing_status'] === 'unavailable') {
                        ?>
                        <button id="create_chat" disabled style="opacity:0.6; cursor:not-allowed;">Unavailable</button>
                        <div class="price-offer">
                            <span class="price">PHP</span>
                            <input type="number" id="price-offer-field" value="<?= htmlspecialchars(number_format($product['price'], 2, '.', '')) ?>" disabled>
                            <button id="make_offer" class="make-offer-btn" disabled style="opacity:0.6; cursor:not-allowed;">Unavailable</button>
                        </div>
                        <?php
                    } else {
                        ?>
                        <button id="create_chat">Chat</button>
                        <div class="price-offer">
                            <span class="price">PHP</span>
                            <input type="number" id="price-offer-field" value="<?= htmlspecialchars(number_format($product['price'], 2, '.', '')) ?>">
                            <button id="make_offer" class="make-offer-btn">Make Offer</button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>

        </div>
    </div>
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['listing_owner_id']) {
                    ?>
    <div class="product-view-recently-viewed">
        <p class="product-view-recently-viewed-header">Recently Viewed</p>
        <div class="products-display">
            <?php foreach ($recentlyVieweds as $listing): ?>
                <?php include './public/components/listing_card.php'  ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php }?>

    <div class="product-view-recently-viewed">
        <p class="product-view-recently-viewed-header">Similar Listings</p>
        <div class="products-display">
            <?php foreach ($similarListings as $listing): ?>
                <?php include './public/components/listing_card.php'  ?>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div>
        <ul class="breadcrumb">
            <li><a href="/">Home</a></li>
            <li><a href="/shop"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li><a href="/shop/electronics"><?= htmlspecialchars($product['subcategory_name']) ?></a></li>
            <li><?= htmlspecialchars($product['name']) ?></li>
        </ul>
    </div>

    <dialog id="unavailable-dialog">
        <p>The product you are looking for does not exist</p>
        <form method="dialog" style="display: flex; justify-content:center">
            <button class="chat-btn" onclick="window.history.back()">Go Back</button>
        </form>
    </dialog>

    <?php if ($product['listing_status'] === 'unavailable'): ?>
    <script>
        document.getElementById('unavailable-dialog').showModal();
       document.getElementById('unavailable-dialog').querySelector('p').focus();
    </script>
    <?php endif; ?>
</main>


<footer>
    <?php include './public/components/footer.php'  ?>
</footer>

<script>
    <?php include './public/javascripts/require_login.js' ?>

document.addEventListener('DOMContentLoaded', () => {
    const galleryImages = document.querySelectorAll('.product-view-gallery img');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = lightbox.querySelector('.lightbox-image');
    const closeBtn = lightbox.querySelector('.lightbox-close');
    const prevBtn = lightbox.querySelector('.lightbox-prev');
    const nextBtn = lightbox.querySelector('.lightbox-next');
    const thumbnailsContainer = lightbox.querySelector('.lightbox-thumbnails');

    let currentIndex = 0;

    galleryImages.forEach((img, index) => {
        const thumb = document.createElement('img');
        thumb.src = img.src;
        thumb.dataset.index = index;
        thumb.addEventListener('click', () => openLightbox(index));
        thumbnailsContainer.appendChild(thumb);
    });

    const thumbs = thumbnailsContainer.querySelectorAll('img');

    function updateActiveThumbnail() {
        thumbs.forEach(t => t.classList.remove('active'));
        thumbs[currentIndex].classList.add('active');
    }

    function openLightbox(index) {
        currentIndex = index;
        lightboxImg.src = galleryImages[currentIndex].src;
        lightbox.classList.add('visible');
        updateActiveThumbnail();
    }

    function closeLightbox() {
        lightbox.classList.remove('visible');
    }

    function showPrev() {
        currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
        lightboxImg.src = galleryImages[currentIndex].src;
        updateActiveThumbnail();
    }

    function showNext() {
        currentIndex = (currentIndex + 1) % galleryImages.length;
        lightboxImg.src = galleryImages[currentIndex].src;
        updateActiveThumbnail();
    }

    galleryImages.forEach((img, index) => {
        img.addEventListener('click', () => openLightbox(index));
    });

    closeBtn.addEventListener('click', closeLightbox);
    prevBtn.addEventListener('click', showPrev);
    nextBtn.addEventListener('click', showNext);

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
        if (lightbox.classList.contains('visible')) {
            if (e.key === 'ArrowLeft') showPrev();
            if (e.key === 'ArrowRight') showNext();
            if (e.key === 'Escape') closeLightbox();
        }
        });
    });


    document.addEventListener('DOMContentLoaded', () => {
        if (typeof initModal === 'function') {
            initModal({
                openBtn: document.getElementById('sellerInfo'),
                dialog: document.getElementById('sellerDialog'),
                closeBtn: document.querySelector('#sellerDialog .seller-dialog-close'),
                content: document.querySelector('#sellerDialog .seller-dialog-content'),
                onOpen: (dialog) => {
                    const rating = document.getElementById('seller-rating')?.textContent || '';
                    const test = document.getElementById('test')?.textContent || '';
                    const listingCount = document.getElementById('listingCount')?.textContent || '';
                    dialog.dataset.reviews = test;
                    dialog.dataset.rating = rating;
                    dialog.dataset.listingCount = listingCount;
                }
            });
        }

        const shareBtn = document.getElementById('share-btn');
        if (shareBtn) {
            shareBtn.addEventListener('click', () => {
                const pageUrl = window.location.href;
                navigator.clipboard.writeText(pageUrl)
                    .then(() => showCopiedMessage('Link copied to clipboard!'))
                    .catch(err => {
                        console.error('Failed to copy:', err);
                        showCopiedMessage('Failed to copy link');
                    });
            });
        }

        function showCopiedMessage(message) {
            const msg = document.createElement('div');
            msg.textContent = message;
            msg.className = 'copied-message';
            document.body.appendChild(msg);
            requestAnimationFrame(() => msg.classList.add('visible'));
            setTimeout(() => {
                msg.classList.remove('visible');
                msg.addEventListener('transitionend', () => msg.remove(), { once: true });
            }, 2000);
        }

        const bookmarkBtn = document.getElementById('product-view-bookmark-btn');
        if (bookmarkBtn) {
            let isBookmarked = parseInt(bookmarkBtn.dataset.bookmarked, 10) === 1;
            const listingsId = parseInt(bookmarkBtn.dataset.listingsId, 10);
            const userId = parseInt(bookmarkBtn.dataset.userId, 10);

            bookmarkBtn.addEventListener('click', () => {
                requireLogin(() => {

                    const url = isBookmarked ? '/includes/unbookmark.php' : '/includes/bookmark.php';
                    const body = `listings_id=${encodeURIComponent(listingsId)}&user_id=${encodeURIComponent(userId)}`;

                    fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                isBookmarked = !isBookmarked;
                                bookmarkBtn.classList.toggle('clicked', isBookmarked);

                                bookmarkBtn.innerHTML = `<img src="/public/assets/images/icons/${isBookmarked ? 'bookmark_added_icon.png' : 'bookmark_icon.png'}" alt="Bookmark">`;
                            } 
                        })
                        .catch(err => console.error(err));
                });
            });
        }
    });

    const listingId = <?php echo (int)$product1['listings_id']; ?>;
    console.log(listingId);

    function postToCreateChat(data) {
    fetch('/includes/create_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
        window.location.href = `/conversation.php?conversation_id=${response.conversation_id}`;
        } else {
        alert(response.error || 'Something went wrong. Please try again.');
        }
    })
    .catch(err => {
        console.error('Request failed:', err);
        alert('Network error. Please check your connection.');
    });
    }

    document.getElementById('create_chat').addEventListener('click', function () {
    postToCreateChat({ listings_id: listingId });
    });

    document.getElementById('make_offer').addEventListener('click', function () {
    const offerField = document.getElementById('price-offer-field');
    const offer = parseFloat(offerField.value);

    if (!offer || offer <= 0) {
        alert("Please enter a valid offer amount.");
        offerField.focus();
        return;
    }

    postToCreateChat({ listings_id: listingId, offer_amount: offer });
    });
</script>

</body>
</html>

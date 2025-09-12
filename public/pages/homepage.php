<?php 
session_start();
include './includes/db_connection.php';
include 'includes/config.php';


$_SESSION['isLoggedIn'] = $_SESSION['isLoggedIn'] ?? false;

$stmtProducts = $pdo->prepare("SELECT * FROM listings");
$stmtProducts->execute();
$products = $stmtProducts->fetchAll();
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
    <div class="category-bar">
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
    </div>
</header>

<main>
    <div class="products-wrapper">
        <div class="products-display">
            <?php foreach ($products as $product): ?>
                <a href="/product.php?id=<?= urlencode($product['listings_id']) ?>" target="_blank" class="listing-card-link">
                    <div class="listing-card" data-lazy-card>
                        <div class="product-image slideshow-container">
                            <div class="slides-track">
                                <?php
                                $images = ["products/" . $product['image_url'], 'product2.jpg'];
                                foreach ($images as $img): ?>
                                    <div class="slide">
                                        <img src="/public/assets/images/<?= htmlspecialchars($img) ?>"
                                             alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <span class="listing-likes">
                                <img src="/public/assets/images/icons/bookmark_icon.png" alt="Bookmark">
                                0
                            </span>

                            <button class="prev" onclick="moveSlide(this, -1)">&#10094;</button>
                            <button class="next" onclick="moveSlide(this, 1)">&#10095;</button>
                        </div>

                        <div class="listing-details">
                            <div class="listing-first-row">
                                <span class="listing-price">
                                    <?= "PHP " . htmlspecialchars(number_format($product['price'], 2)); ?>
                                </span>
                            </div>
                            <p class="listing-title"><?= htmlspecialchars($product['name']); ?></p>
                            <div class="other-details">
                                <span class="listing-condition"><?= html_entity_decode($product['condition']) ?></span>
                                <span class="listing-time">
                                    <?php
                                    date_default_timezone_set('Asia/Manila');
                                    $postedTime = new DateTime($product['date_created']);
                                    $currentTime = new DateTime();
                                    $interval = $postedTime->diff($currentTime);

                                    if ($interval->days > 0) {
                                        echo $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ago';
                                    } elseif ($interval->h > 0) {
                                        echo $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                                    } elseif ($interval->i > 0) {
                                        echo $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                                    } else {
                                        echo 'Just now';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

</main>

<footer>
    <?php include './public/components/footer.php'  ?>
</footer>

<script>

    function categ_myFunction() {
        document.getElementById("categ-myDropdown").classList.toggle("show");
    }

    window.addEventListener('click', function(event) {
        if (!event.target.matches('.categ-dropbtn')) {
            document.querySelectorAll(".categ-dropdown-content.show")
                .forEach(dropdown => dropdown.classList.remove('show'));
        }
    });

    document.querySelectorAll('.slide img').forEach(img => {
    img.addEventListener('load', () => {
        img.classList.add('loaded');
    });
    });

    document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.slideshow-container').forEach(container => {
        const track = container.querySelector('.slides-track');
        let slides = Array.from(track.children);
        let slideCount = slides.length;

        const firstClone = slides[0].cloneNode(true);
        const lastClone = slides[slideCount - 1].cloneNode(true);
        track.appendChild(firstClone);
        track.insertBefore(lastClone, slides[0]);

        slides = Array.from(track.children);
        slideCount = slides.length - 2;

        let index = 1;
        let slideWidth = container.getBoundingClientRect().width;
        track.style.transform = `translateX(${-slideWidth * index}px)`;

        function updateWidth() {
            slideWidth = container.getBoundingClientRect().width;
            track.style.transition = 'none';
            track.style.transform = `translateX(${-slideWidth * index}px)`;
        }
        window.addEventListener('resize', updateWidth);

        function moveSlide(n) {
            if (track.classList.contains('shifting')) return;
            track.classList.add('shifting');
            index += n;
            track.style.transition = 'transform 0.5s ease';
            track.style.transform = `translateX(${-slideWidth * index}px)`;
        }

        track.addEventListener('transitionend', () => {
            track.classList.remove('shifting');
            if (index === 0) {
                index = slideCount;
                track.style.transition = 'none';
                track.style.transform = `translateX(${-slideWidth * index}px)`;
            }
            if (index === slideCount + 1) {
                index = 1;
                track.style.transition = 'none';
                track.style.transform = `translateX(${-slideWidth * index}px)`;
            }
        });

        const prevBtn = container.querySelector('.prev');
        const nextBtn = container.querySelector('.next');

        prevBtn.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            moveSlide(-1);
        });

        nextBtn.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            moveSlide(1);
        });

        if (window.innerWidth <= 767) {
            let startX = 0;
            let endX = 0;

            container.addEventListener('touchstart', e => {
                startX = e.touches[0].clientX;
            });

            container.addEventListener('touchmove', e => {
                endX = e.touches[0].clientX;
            });

            container.addEventListener('touchend', () => {
                const diff = startX - endX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) {
                        moveSlide(1);
                    } else {
                        moveSlide(-1);
                    }
                }
                startX = 0;
                endX = 0;
            });
        }
    });
});


    </script>
</body>
</html>


<a href="/product.php?id=<?= urlencode($product['listings_id']) ?>" target="_blank" class="listing-card-link">
    <div class="listing-card" data-lazy-card>
        <div class="product-image slideshow-container">
            <div class="slides-track">
                <?php foreach ($product['images'] as $img): ?>
                    <div class="slide">
                        <img src="/public/assets/images/products/<?= htmlspecialchars($img['url']) ?>" 
                            alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="prev">&#10094;</button>
            <button class="next">&#10095;</button>
        </div>

        <div class="listing-details">
            <div class="listing-first-row">
                <span class="listing-title">
                    <?= htmlspecialchars($product['name']); ?>
                </span>
            </div>
            <div class="listing-price-wrapper">

                <?php if (!empty($product['discount']) && $product['discount'] > 0): ?>
                    <?php
                        $discountedPrice = $product['price'] - ($product['price'] * ($product['discount'] / 100));
                    ?>
                    <span class="listing-price-new">
                        <?= "PHP " . htmlspecialchars(number_format($discountedPrice, 2)); ?>
                    </span>
                    <div>
                        <span class="listing-price-old">
                            <?= "PHP " . htmlspecialchars(number_format($product['price'], 2)); ?>
                        </span>
                        <span class="listing-price-discount">
                            <?= "-" . htmlspecialchars($product['discount']) . "%"; ?>
                        </span>
                    </div>
                <?php else: ?>
                    <span class="listing-price-new">
                        <?= "PHP " . htmlspecialchars(number_format($product['price'], 2)); ?>
                    </span>
                <?php endif; ?>

            </div>
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

<script>
document.addEventListener('DOMContentLoaded', initSlideshows);

function initSlideshows() {
    document.querySelectorAll('.slideshow-container:not([data-initialized])')
        .forEach(container => {
            container.dataset.initialized = "true";
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
}
</script>

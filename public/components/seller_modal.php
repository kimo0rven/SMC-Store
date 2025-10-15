<?php
require './includes/db_connection.php';

$query = "
    SELECT COUNT(*)
    FROM follow
    WHERE follower_id = :follower_id
    AND followed_id = :user_id
";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'follower_id' => (int)$_SESSION['user_id'],
    'user_id'     => (int)$product['listing_owner_id']
]);
$follows = $stmt->fetchColumn();
$isFollowing = ($follows > 0);
?>

<dialog id="sellerDialog">
    <div class="seller-dialog-header">
        <h2 style="padding: 0; margin: 0;">About this seller</h2>
        <span class="seller-dialog-close" aria-label="Close">&times;</span>
    </div>

    <div class="seller-dialog-content">
        <div class="seller-content-left">
            <div class="seller-content-top">
                <div >
                    <img src="./public/assets/images/avatars/<?= htmlspecialchars($product['avatar']) ?>" alt="<?= htmlspecialchars($product['first_name'] . " " . $product['last_name']) ?>">
                </div>

                <div style="display: flex; flex-direction: column">
                    <span id="seller-content-name-id" class="seller-content-name">
                        <a><?= htmlspecialchars($product['first_name'] . " " . $product['last_name']) ?></a>
                    </span>
                    <div class="seller-content-sub">
                        <span id="seller-content-rating">100% positive</span>
                        <span id="seller-content-sold-items" style="color: #6a6a6a">1000 items sold</span>
                    </div>
                    
                </div>

            </div>
            <div class="seller-joined-month-container">
                <img class="seller-joined-month" src="./public/assets/images/icons/calendar_icon.png" alt="">
                <span>Joined <?= htmlspecialchars(date("M Y", strtotime($product['user_date_created']))) ?></span>
            </div>

            <div class="seller-dialog-actions">
                <?php if ((int)$product['listing_owner_id'] === (int)$_SESSION['user_id']): ?>
                    <button id="seller-dialog-contact-btn"  
                            data-seller="<?= (int)$product['listing_owner_id'] ?>"
                            data-user="<?= (int)$_SESSION['user_id'] ?>"
                            data-follow="<?= $isFollowing ? 1 : 0 ?>" style="display:none;">Contact</button>
                    <button id="seller-dialog-visit-btn">Visit Store</button>
                <?php else: ?>
                    <button id="seller-dialog-follow-btn"
                            data-seller="<?= (int)$product['listing_owner_id'] ?>"
                            data-user="<?= (int)$_SESSION['user_id'] ?>"
                            data-follow="<?= $isFollowing ? 1 : 0 ?>"
                            class="<?= $isFollowing ? 'followed' : '' ?>">
                        <?php if ($isFollowing): ?>
                            Following
                        <?php else: ?>
                            <img src="/public/assets/images/icons/follow_user_icon.png" alt=""> Follow Seller
                        <?php endif; ?>
                    </button>
                    <button id="seller-dialog-contact-btn">Contact</button>
                    <button id="seller-dialog-visit-btn">Visit Store</button>
                <?php endif; ?>
            </div>

        </div>
        <div class="seller-content-right">

            <div style="display: flex; flex-direction:row; align-items: center; gap: 0.5rem">
                <h3 style="padding: 0; margin: 0;">Seller feedback </h3><span id="seller-content-review-count"></span>
            </div>
            <div class="seller-content-review-wrapper">
                <div id="seller-content-reviews-container">
                    <!-- <div class="seller-content-reviews-header">
                        <div id="seller-name">Seller Name</div>
                        <div>Past Year</div>
                    </div>
                    <div class="seller-content-reviews-display" id="reviews-display">
                        No Reviews Yet
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</dialog>

<script>
    <?php include './public/javascripts/require_login.js' ?>
    function initModal({ openBtn, dialog, closeBtn, content, onOpen }) {
        function lockScroll() {
            const scrollBarComp = window.innerWidth - document.documentElement.clientWidth;
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            if (scrollBarComp > 0) {
                document.documentElement.style.paddingRight = scrollBarComp + 'px';
            }
        }

        function unlockScroll() {
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            document.documentElement.style.paddingRight = '';
        }

        function closeWithAnimation() {
            dialog.classList.add('closing');
            dialog.addEventListener('animationend', () => {
                dialog.classList.remove('closing');
                dialog.close();
                unlockScroll();
            }, { once: true });
        }

        function maskName(name) {
            if (!name) return '';
                return name[0] + '*'.repeat(name.length - 1);
            }

        function timeAgo(dateString) {
            const now = new Date();
            const past = new Date(dateString);

            let years = now.getFullYear() - past.getFullYear();
            let months = now.getMonth() - past.getMonth();
            let days = now.getDate() - past.getDate();

            if (days < 0) {
                months--;
                const prevMonth = new Date(now.getFullYear(), now.getMonth(), 0).getDate();
                days += prevMonth;
            }

            if (months < 0) {
                years--;
                months += 12;
            }

            if (years > 0) {
                return years === 1 ? "a year ago" : `${years} years ago`;
            } else if (months > 0) {
                return months === 1 ? "a month ago" : `${months} months ago`;
            } else if (days > 0) {
                return days === 1 ? "a day ago" : `${days} days ago`;
            } else {
                return "today";
            }
        }

        openBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (typeof onOpen === 'function') {
                onOpen(dialog);
            }

            const reviewsdata = dialog.dataset.reviews;
            const reviews = JSON.parse(reviewsdata);
            const rating = dialog.dataset.rating;
            const itemsSold = dialog.dataset.listingCount;

            document.getElementById('seller-content-rating').textContent = rating;

            document.getElementById('seller-content-sold-items').textContent = parseInt(itemsSold, 10) === 0 ? 'No items sold' : `${parseInt(itemsSold, 10)} item${parseInt(itemsSold, 10) === 1 ? '' : 's'} sold`;

            document.getElementById('seller-content-review-count').textContent = "(" + JSON.parse(dialog.dataset.reviews).length + ")";

            // document.getElementById('seller-dialog-follow-btn').dataset.seller = dialog.dataset.sellerId;

            const wrapper = document.getElementsByClassName('seller-content-review-wrapper')[0];
            if (reviews.length > 0) {
                reviews.forEach(review => {

                    const container = document.createElement('div');
                    container.classList.add('seller-content-reviews-container');
                    wrapper.appendChild(container)

                    const header = document.createElement('div');
                    header.classList.add('seller-content-reviews-header');
                    
                    const name = document.createElement('div');
                    name.classList.add('seller-content-reviews-name');
                    if(review.anonymous) {
                        name.textContent = maskName(review.first_name) + " " + maskName(review.last_name);
                    } else {
                        name.textContent = review.first_name + " " + review.last_name;
                    }

                    const reviewTime = document.createElement('div');
                    reviewTime.textContent = timeAgo(review.review_datetime);

                    header.appendChild(name)
                    header.appendChild(reviewTime)

                    const rating = document.createElement('div');
                    rating.classList.add('seller-content-reviews-rating');
                    const maxStars = 5;
                    const score = parseFloat(review.rating);
                    for (let i = 1; i <= maxStars; i++) {
                        const starImg = document.createElement('img');
                        starImg.classList.add('star-icon');

                        if (score >= i) {
                            starImg.src = '/public/assets/images/icons/star_icon.png';
                            starImg.alt = '★';
                        } else if (score >= i - 0.5) {
                            starImg.src = '/public/assets/images/icons/star_half_icon.png';
                            starImg.alt = '☆½';
                        } else {
                            starImg.src = '/public/assets/images/icons/star_empty_icon.png';
                            starImg.alt = '☆';
                        }

                        rating.appendChild(starImg);
                    }
                    container.appendChild(rating);

                    const reviewContent = document.createElement('div');
                    reviewContent.classList.add('seller-content-reviews-display');
                    reviewContent.textContent = review.review_content;

                    const reviewItem = document.createElement('div');
                    reviewItem.classList.add('seller-content-reviews-item-reviewed');
                    reviewItem.textContent = review.listing_name;

                    container.appendChild(header);
                    container.appendChild(rating);
                    container.appendChild(reviewContent);
                    container.appendChild(reviewItem);
                });

            } else {
                wrapper.textContent = "No Reviews";
            }

            dialog.showModal();
            document.activeElement.blur();
            lockScroll();
        });

        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeWithAnimation();
        });

        dialog.addEventListener('click', (e) => {
            if (content && !content.contains(e.target)) {
                closeWithAnimation();
            }
        });

        dialog.addEventListener('close', unlockScroll);
    }

    const followBtn  = document.querySelector('.seller-dialog-actions button');
    const contactBtn = document.getElementById('seller-dialog-contact-btn');

    let sellerId = parseInt(followBtn.dataset.seller, 10);
    const userId   = parseInt(followBtn.dataset.user, 10);
    let followCount = parseInt(followBtn.dataset.follow, 10);

    console.log(followBtn.dataset)

    
    if (sellerId === userId) {
        followBtn.style.display = 'none';
        contactBtn.style.display = 'none';
    } else {
        if (followCount > 0) {
            followBtn.classList.add('followed');
            followBtn.textContent = 'Following';
        } else {
            followBtn.classList.remove('followed');
            followBtn.innerHTML = '<img src="/public/assets/images/icons/follow_user_icon.png"> Follow Seller';
        }
    }

    followBtn.addEventListener('click', () => {
        requireLogin(() => {
            if (sellerId === userId) return;

            if (followCount > 0) {
                fetch('/includes/unfollow.php', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `seller_id=${encodeURIComponent(sellerId)}&user_id=${encodeURIComponent(userId)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        followCount = 0;
                        followBtn.dataset.follow = "0";
                        followBtn.classList.remove('followed');
                        followBtn.innerHTML = '<img src="/public/assets/images/icons/follow_user_icon.png"> Follow Seller';
                    }
                });
            } else {
                fetch('/includes/follow.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `seller_id=${encodeURIComponent(sellerId)}&user_id=${encodeURIComponent(userId)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        followCount = 1;
                        followBtn.dataset.follow = "1";
                        followBtn.classList.add('followed');
                        followBtn.textContent = 'Following';
                    }
                });
            }
        });
    });

    const storeBtn = document.getElementById('seller-dialog-visit-btn');
    const sellerName = document.getElementById('seller-content-name-id');
    [storeBtn, sellerName].forEach(btn => {
        btn.addEventListener('click', () => {
            window.location.href = "/user.php?id=" + sellerId;
        });
    });

    document.getElementById('seller-dialog-contact-btn').addEventListener('click', function () {
        postToCreateChat({ listings_id: listingId }, 'create_chat');
    });

</script>


<style>
.dropdown {
    position: relative;
    display: inline-block;
}

.dropbtn {
    background: none;
    border: none;
    color: #ffffffff;
    font: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
}

.arrow {
    width: 16px;
    height: 16px;
    background-image: url('/public/assets/images/icons/arrow_down.svg');
    background-size: contain;
    background-repeat: no-repeat;
    transition: transform 0.3s ease;
}

.dropdown-content {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    margin-top: 4px;
    border-radius: .5rem;
    z-index: 999;
}

.dropdown-content a {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 12px 16px;
    text-decoration: none;
    color: #333;
    justify-content: flex-start;
}

.dropdown-content img {
    height: auto;
    width: 1.5;
}

.dropdown-content a:hover {
    background-color: #ddd;
    border-radius: .5rem;

}

.dropdown.open .dropdown-content {
    display: block;
}

.dropdown.open .arrow {
    transform: rotate(180deg);
}

</style>

<?php
$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<div class="dropdown" id="user-dropdown">
    <button class="dropbtn" aria-haspopup="true" aria-expanded="false">
        Hello, <?php echo $user['first_name']; ?>
        <span class="arrow" aria-hidden="true"></span>
    </button>

    <div class="dropdown-content" role="menu">
        <a href="#"><img src="/public/assets/images/icons/profile_icon.png" alt=""> Profile</a>
        <a href="#"><img src="/public/assets/images/icons/settings_icon.png" alt=""> Settings</a>
        <a href="#"><img src="/public/assets/images/icons/notifications_icon.png" alt=""> Notifications</a>
        <a href=""><img src="/public/assets/images/icons/bookmark_icon.png" alt=""> Likes</a>
        <a href=""><img src="/public/assets/images/icons/listings_icon.png" alt=""> Manage Listing</a>
        <a href="/logout.php"><img src="/public/assets/images/icons/logout_icon.png" alt=""> Log out</a>
    </div>
</div>

<script>
    const dropdown = document.getElementById('user-dropdown');
    const button = dropdown.querySelector('.dropbtn');

    button.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = dropdown.classList.toggle('open');
        button.setAttribute('aria-expanded', isOpen);
    });

    document.addEventListener('click', () => {
        if (dropdown.classList.contains('open')) {
        dropdown.classList.remove('open');
        button.setAttribute('aria-expanded', false);
        }
    });
</script>





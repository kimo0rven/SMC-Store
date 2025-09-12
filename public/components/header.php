<?php
include './includes/db_connection.php';

$isLoggedIn = !empty($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] === true;
?>

<div class="main-header">
    <div>
        <a href="/"><img src="/public/assets/images/temp_logo.png" style="height:auto; width:64px;" alt="Logo"></a>
    </div>
    <div class="search">
        <input type="search" placeholder="Search..">
    </div>
    <nav class="nav">
        <?php if($isLoggedIn) {
            include './public/components/loggedin_dropdown.php';
        } else {
            echo '<div id="open-login-modal-button">Login</div>';
        } ?>
        <div id="sell-nav"><button id="sell-button">Sell</button></div>
    </nav>
</div>

<dialog id="login-modal">
    <?php include 'public/pages/login.php'; ?>
</dialog>

<script>
    const isLoggedIn = <?= json_encode($isLoggedIn) ?>;

    <?php if (!empty($_SESSION['error_message'])): ?>
        const loginModal = document.getElementById('login-modal');
        const errorEl = document.createElement('p');
        errorEl.style.textAlign = 'center';
        errorEl.className = 'login-error-message';
        errorEl.textContent = <?= json_encode($_SESSION['error_message']) ?>;
        loginModal.querySelector('form').prepend(errorEl);
        loginModal.showModal();
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    const modal = document.getElementById('login-modal');
    const openButton = document.getElementById('open-login-modal-button');
    const closeButton = document.getElementById('close-login-modal-button');
    const sellButton = document.getElementById('sell-button');

    if (openButton) {
        openButton.addEventListener('click', () => modal.showModal());
    }

    function closeDialogWithAnimation() {
        modal.classList.add('closing');
        modal.addEventListener('animationend', () => {
            modal.classList.remove('closing');
            modal.close();
        }, { once: true });
    }

    if (closeButton) {
        closeButton.addEventListener('click', closeDialogWithAnimation);
    }

    modal.addEventListener('click', (event) => {
        const dialogDimensions = modal.getBoundingClientRect();
        if (
            event.clientX < dialogDimensions.left ||
            event.clientX > dialogDimensions.right ||
            event.clientY < dialogDimensions.top ||
            event.clientY > dialogDimensions.bottom
        ) {
            closeDialogWithAnimation();
        }
    });

    sellButton.addEventListener('click', () => {
        if (!isLoggedIn) {
            modal.showModal();
        } else {
            window.location.href = '/sell.php'; 
        }
    });
</script>

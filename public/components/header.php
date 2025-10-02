<?php
include './includes/db_connection.php';

$isLoggedIn = !empty($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] === true;
?>

<div class="main-header">
    <div>
        <a href="/"><img src="/public/assets/images/temp_logo.png" style="height:auto; width:64px;" alt="Logo"></a>
    </div>
    <div class="search">
        <form action="/" method="get">
            <input type="search" name="search_query" placeholder="Search products...">
        </form>
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
    <?php include 'public/components/login_modal.php'; ?>
</dialog>

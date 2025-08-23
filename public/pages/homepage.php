<?php 
include 'includes/config.php';
?>

<html lang="en">
<head>

    <title><?php echo $title . " | Your Campus, Your Store" ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">

</head>
<body>
    <header>
        <div>
            <img src="/public/assets/images/temp_logo.png" height="auto" width="64px" alt="">
        </div>
        <div class="search">
            <input type="search" placeholder="Search..">
        </div>

        <nav class="nav">
            <div>Register</div>
            <div>Login</div>
            <div><button>Sell</button></div>
        </nav>
    </header>
    <main><h1> Hello World</h1></main>
    <footer>
        <div>
            <div>
                <p>Top Searches</p>
            </div>

            <div>zara | iphone | uniqlo | ipad | dress | bag | digicam | lululemon | bag | tote bag | digital camera | lacoste | kindle | carhartt | miu miu | laptop | nike | backpack | longchamp | filipiniana | prada | love bonito | crocs | adidas | balenciaga | dior | jacket | onitsuka | seiko | wallet | boots | vivienne westwood | chanel | iphone 15 | macbook | camera | vintage | stussy | birkenstock | sofa | charles and keith | cardigan | fujifilm | kate spade | watch | iphone 12 | loewe | nintendo switch</div>
        </div>

        <div class=bottom-footer>
            <div><img src="/public/assets/images/temp_logo.png" height="auto" width="32px" alt=""></div>
            <div><?php echo "© ". date("Y") . " " . $title ?></div>
        </div>
    </footer>
</body>
</html>

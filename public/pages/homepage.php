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
            <!-- <div>Register</div> -->
             <div><img src="/public/assets/images/icons/cart_icon.png" alt=""></div>
            <div onclick="">Login</div>
            <div><button>Sell</button></div>
        </nav>
    </header>

    <main>
        <div class="product-display-container">
            <?php for($x = 0; $x <= 15; $x++) { ?>
                <div class="product-container" id="product-container">
                    <div>
                        <img src="/public/assets/images/dummy_product2.jpg" alt="">
                    </div>
                    <div>
                        <h2 class="item-name">Item Name #<?php echo $x ?></h2>
                        <p class="item-price">₱100.00</p>
                    </div>
                </div>
            <?php } ?>
        </div>
        
    </main>

    <dialog id="product-dialog-modal">
        <div class="product-full-page-container">
            <div class="product-full-page-tags-breadcrumb">
                <p>Home > Category Name</p>
            </div>
            <div class="carousel-container">
                <div class="wrapper">
                        <i id="left" class="fa-solid  fas fa-angle-left"></i>
                        <ul class="carousel">
                            <li class="card">
                                <div class="img"><img src="https://media.geeksforgeeks.org/wp-content/uploads/20240213150115/ppp.png" alt="" draggable="false"> </div>
                            </li>
                            <li class="card">
                                <div class="img"><img src="/public/assets/images/product1.jpg" alt="" draggable="false"> </div>
                            </li>
                            <li class="card">
                                <div class="img"><img src="https://media.geeksforgeeks.org/wp-content/uploads/20240213150115/ppp.png" alt="" draggable="false"> </div>
                            </li>
                            <li class="card">
                                <div class="img"><img src="https://media.geeksforgeeks.org/wp-content/uploads/20240213150115/ppp.png" alt="" draggable="false"> </div>
                            </li>
                            <li class="card">
                                <div class="img"><img src="https://media.geeksforgeeks.org/wp-content/uploads/20240213150115/ppp.png" alt="" draggable="false"> </div>
                            </li>
                            <li class="card">
                                <div class="img"><img src="https://media.geeksforgeeks.org/wp-content/uploads/20240213150115/ppp.png" alt="" draggable="false"> </div>
                            </li>
                        </ul>
                        <i id="right" class="fa-solid fas fa-angle-right"></i>
                    </div>
            </div>
        <h2>Modal title</h2>
        <p>This is a plain modal using the HTML dialog element.</p>
        <form method="dialog">
            <button value="close">Close</button>
        </form>
        </div>
    </dialog>

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

    <script>
        const productContainers = document.querySelectorAll('.product-container');
        const modal = document.getElementById('product-dialog-modal');

        const carousel = document.querySelector(".carousel");
        const arrowBtns = document.querySelectorAll(".wrapper i");
        let firstCardWidth = 0;

        function updateCardWidth() {
            firstCardWidth = carousel.querySelector(".card").offsetWidth;
        }

        productContainers.forEach(container => {
            container.addEventListener('click', () => {

                modal.showModal();
                updateCardWidth();
            });
        });

        modal.addEventListener('click', (event) => {
            const rect = modal.getBoundingClientRect();
            const inDialog =
            event.clientX >= rect.left &&
            event.clientX <= rect.right &&
            event.clientY >= rect.top &&
            event.clientY <= rect.bottom;

            if (!inDialog) modal.close();
        });

        modal.addEventListener('cancel', (e) => {
            // e.preventDefault();
        });

        document.addEventListener("DOMContentLoaded", function() {
    const carousel = document.querySelector(".carousel");
    const arrowBtns = document.querySelectorAll(".wrapper i");
    const wrapper = document.querySelector(".wrapper");

    const firstCard = carousel.querySelector(".card");
    const firstCardWidth = firstCard.offsetWidth;

    let isDragging = false,
        startX,
        startScrollLeft,
        timeoutId;

    const dragStart = (e) => { 
        isDragging = true;
        carousel.classList.add("dragging");
        startX = e.pageX;
        startScrollLeft = carousel.scrollLeft;
    };

    const dragging = (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const newScrollLeft = startScrollLeft - (e.pageX - startX);
    

        if (newScrollLeft <= 0 || newScrollLeft >= 
            carousel.scrollWidth - carousel.offsetWidth) {
            
            isDragging = false;
            return;
        }
    
        carousel.scrollLeft = newScrollLeft;
    };

    const dragStop = () => {
        isDragging = false; 
        carousel.classList.remove("dragging");
    };

    const autoPlay = () => {
    
        if (window.innerWidth < 800) return; 

        const totalCardWidth = carousel.scrollWidth;
        const maxScrollLeft = totalCardWidth - carousel.offsetWidth;

        if (carousel.scrollLeft >= maxScrollLeft) return;
        
        timeoutId = setTimeout(() => 
            carousel.scrollLeft += firstCardWidth, 2500);
    };

    carousel.addEventListener("mousedown", dragStart);
    carousel.addEventListener("mousemove", dragging);
    document.addEventListener("mouseup", dragStop);
    wrapper.addEventListener("mouseenter", () => 
        clearTimeout(timeoutId));
    wrapper.addEventListener("mouseleave", autoPlay);

    arrowBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            updateCardWidth();
            carousel.scrollLeft += btn.id === "left" ? -firstCardWidth : firstCardWidth;
        });
    });
});

    </script>
</body>
</html>

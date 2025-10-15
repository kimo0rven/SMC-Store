<?php
session_start();

if (empty($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: index.php");
    exit();
}

require './includes/db_connection.php';

$categQuery = "SELECT * FROM categories";
$stmt = $pdo->prepare($categQuery);
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell | Michaelite Store</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">
</head>
<body>
<header>
    <?php include './public/components/header.php' ?>
</header>

<main>
    <div style="margin: 5rem" class="flex column">
        <div>
            <h3 style="font-size:2.25rem;text-transform:uppercase;margin:0">Sell your product</h3>
        </div>
        <div>
            <form action="./includes/submit_listing.php" method="POST" enctype="multipart/form-data" class="sell-page-container">
        
        <div class="sell-page-upload-page">
            <label class="upload-container">
                <img src="/public/assets/images/icons/image_upload_icon.png" alt="">
                <p>Click to select images<span><br>(Up to 6 photos)</span></p>

                <input type="file" name="images[]" accept="image/*" multiple>
                <br><small id="file-count">No files chosen</small>
            </label>

            <div class="preview" id="preview"></div>
        </div>

        <div class="sell-page-details-page">
            <h3 class="sell-page-details-page-title">Product Details</h3>
            <div class="sell-page-details-category-wrapper">
                <input type="hidden" name="category" id="sell-page-details-category-input">
                <input type="hidden" name="subcategory" id="sell-page-details-subcategory-input">

                <div class="sell-page-details-dropdown" id="sell-page-details-category-dropdown">
                    <div class="sell-page-details-dropdown-selected">Select Category</div>
                    <div class="sell-page-details-dropdown-list">
                        <input type="text" placeholder="Search..." class="sell-page-details-dropdown-search">
                        <ul>
                            <?php foreach($categories as $category): ?>
                                <li data-value="<?= htmlspecialchars($category['category_id']) ?>">
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div>
                <div class="sell-page-details-dropdown" id="sell-page-details-subcategory-dropdown" style="display:none;">
                    <div class="sell-page-details-dropdown-selected">Select Subcategory</div>
                    <div class="sell-page-details-dropdown-list">
                        <input type="text" placeholder="Search..." class="sell-page-details-dropdown-search">
                        <ul></ul>
                    </div>
                </div>
            </div>

            <div>
                <input placeholder="Listing Title" type="text" name="title" required>
            </div>

            <input type="text" name="brand" placeholder="Brand" required>

            <div id="select-test">
                <span>Condition</span>
                <select name="condition" required>
                    <option value="Brand New">Brand New</option>
                    <option value="Like New">Like New</option>
                    <option value="Lightly Used">Lightly Used</option>
                    <option value="Well Used">Well Used</option>
                    <option value="Heavily Used">Heavily Used</option>
                    <option value="Refurbished">Refurbished</option>
                </select>
            </div>

            <div class="sell-page-details-other">
                <div>
                    <span>Price</span>
                    <input type="number" name="price" min="0" step="0.01" required>
                </div>
                <div>
                    <span>Stock</span>
                    <input type="number" name="stock" min="1" required>
                </div>
            </div>

            <div>
                <span>Description</span>
                <textarea rows="15" name="description" required></textarea>
            </div>

            <div class="sell-page-details-last">
                <button class="chat-btn" type="submit">Post Listing</button>
            </div>
        </div>
    </form>
        </div>
    </div>
</main>

<footer>
    <?php include './public/components/footer.php' ?>
</footer>

<script src="./public/javascripts/category_dropdown.js"></script>

<script>
const fileInput = document.querySelector('input[type="file"][name="images[]"]');
const fileCount = document.getElementById('file-count');
const preview = document.getElementById('preview');

fileInput.addEventListener('change', () => {
    const files = Array.from(fileInput.files);
    preview.innerHTML = '';

    if (files.length > 6) {
        alert('You can only upload up to 6 images.');
        fileInput.value = '';
        fileCount.textContent = 'No files chosen';
        return;
    }

    const invalidFiles = files.filter(file => !file.type.startsWith('image/'));
    if (invalidFiles.length > 0) {
        alert('Only image files are allowed.');
        fileInput.value = '';
        fileCount.textContent = 'No files chosen';
        return;
    }

    if (files.length === 0) {
        fileCount.textContent = 'No files chosen';
    } else if (files.length === 1) {
        fileCount.textContent = '1 file chosen';
    } else {
        fileCount.textContent = `${files.length} files chosen`;
    }

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const item = document.createElement('div');
            item.classList.add('preview-item');

            const img = document.createElement('img');
            img.src = e.target.result;

            item.appendChild(img);
            preview.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
});
</script>


</body>
</html>

<?php
session_start();
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
    <form action="./includes/submit_listing.php" method="POST" enctype="multipart/form-data" class="sell-page-container">
        <div class="sell-page-upload-page">
            <div class="upload-container" id="upload-container">
                <img src="/public/assets/images/icons/image_upload_icon.png" alt="">
                <p>Click or drag images here<span><br>(Up to 6 photos)</span></p>
                <input type="file" name="images[]" id="file-input" accept="image/*" multiple hidden>
            </div>
            <span>&#128161; Tip: Re-arrange photos to change cover</span>
            <div class="preview" id="preview"></div>
        </div>

        <div class="sell-page-details-page">
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
                <button type="submit">Post Listing</button>
            </div>
        </div>
    </form>
</main>

<footer>
    <?php include './public/components/footer.php' ?>
</footer>

<script src="/public/js/sell-page.js"></script>

<script>
const uploadContainer = document.getElementById('upload-container');
const fileInput = document.getElementById('file-input');
const preview = document.getElementById('preview');

let filesArray = [];

// Click to open file picker
uploadContainer.addEventListener('click', () => fileInput.click());
fileInput.addEventListener('change', handleFiles);

// Drag & drop upload (still allowed for adding files)
uploadContainer.addEventListener('dragover', e => {
    e.preventDefault();
    uploadContainer.classList.add('dragover');
});
uploadContainer.addEventListener('dragleave', () => {
    uploadContainer.classList.remove('dragover');
});
uploadContainer.addEventListener('drop', e => {
    e.preventDefault();
    uploadContainer.classList.remove('dragover');
    handleFiles({ target: { files: e.dataTransfer.files } });
});

// Handle adding files
function handleFiles(e) {
    const newFiles = Array.from(e.target.files);

    if (filesArray.length + newFiles.length > 6) {
        alert('You can only upload up to 6 images.');
        return;
    }

    newFiles.forEach(file => {
        if (!file.type.match(/^image\/(jpeg|png|webp|gif)$/)) {
            alert(`"${file.name}" is not a supported image format.`);
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            file.previewSrc = reader.result;
            filesArray.push(file);

            const item = document.createElement('div');
            item.classList.add('preview-item');

            const img = document.createElement('img');
            img.src = file.previewSrc;

            const removeBtn = document.createElement('button');
            removeBtn.innerHTML = '×';
            removeBtn.classList.add('remove-btn');
            removeBtn.addEventListener('click', () => {
                filesArray = filesArray.filter(f => f !== file);
                item.remove();
                updateCoverLabel();
            });

            item.appendChild(img);
            item.appendChild(removeBtn);
            preview.appendChild(item);

            updateCoverLabel();
        };
        reader.readAsDataURL(file);
    });

    fileInput.value = '';
}

// Update cover label (first image only)
function updateCoverLabel() {
    preview.querySelectorAll('.cover-label').forEach(label => label.remove());
    const firstItem = preview.querySelector('.preview-item');
    if (firstItem) {
        const label = document.createElement('div');
        label.classList.add('cover-label');
        label.textContent = 'Cover';
        firstItem.appendChild(label);
    }
}

// Dropdown init (unchanged)
function initSellPageDetailsDropdown(dropdownId, hiddenInputId, onSelect) {
    const dropdown = document.getElementById(dropdownId);
    const selected = dropdown.querySelector('.sell-page-details-dropdown-selected');
    const listContainer = dropdown.querySelector('.sell-page-details-dropdown-list');
    const searchInput = dropdown.querySelector('.sell-page-details-dropdown-search');
    const list = dropdown.querySelector('ul');
    const hiddenInput = document.getElementById(hiddenInputId);

    selected.addEventListener('click', () => {
        const isOpen = listContainer.style.display === 'flex';
        document.querySelectorAll('.sell-page-details-dropdown-list').forEach(dl => dl.style.display = 'none');
        listContainer.style.display = isOpen ? 'none' : 'flex';
        searchInput.value = '';
        filterList('');
        searchInput.focus();
    });

    function filterList(term) {
        Array.from(list.children).forEach(li => {
            li.style.display = li.textContent.toLowerCase().includes(term.toLowerCase()) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', () => {
        filterList(searchInput.value);
    });

    list.addEventListener('click', e => {
        if (e.target.tagName === 'LI') {
            selected.textContent = e.target.textContent;
            selected.dataset.value = e.target.dataset.value;
            hiddenInput.value = e.target.dataset.value;
            listContainer.style.display = 'none';
            if (onSelect) onSelect(e.target.dataset.value, e.target.textContent);
        }
    });

    document.addEventListener('click', e => {
        if (!dropdown.contains(e.target)) {
            listContainer.style.display = 'none';
        }
    });
}

initSellPageDetailsDropdown(
    'sell-page-details-category-dropdown',
    'sell-page-details-category-input',
    (categoryId) => {
        const subDropdown = document.getElementById('sell-page-details-subcategory-dropdown');
        const subList = subDropdown.querySelector('ul');
        const subSelected = subDropdown.querySelector('.sell-page-details-dropdown-selected');
        const subHiddenInput = document.getElementById('sell-page-details-subcategory-input');

        subList.innerHTML = '';
        subSelected.textContent = 'Select Subcategory';
        subHiddenInput.value = '';
        subDropdown.style.display = 'none';

        if (!categoryId) return;

        fetch(`./includes/get_subcategories.php?category_id=${categoryId}`)
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    data.forEach(sub => {
                        const li = document.createElement('li');
                        li.dataset.value = sub.subcategory_id;
                        li.textContent = sub.subcategory_name;
                        subList.appendChild(li);
                    });
                    subDropdown.style.display = 'block';

                    if (!subDropdown.dataset.initialized) {
                        initSellPageDetailsDropdown(
                            'sell-page-details-subcategory-dropdown',
                            'sell-page-details-subcategory-input'
                        );
                        subDropdown.dataset.initialized = 'true';
                    }
                }
            })
            .catch(err => console.error(err));
    }
);

document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();

    const category = document.getElementById('sell-page-details-category-input').value;
    const subcategory = document.getElementById('sell-page-details-subcategory-input').value;
    if (!category || !subcategory) {
        alert("Please select both category and subcategory.");
        return;
    }

    const dataTransfer = new DataTransfer();
    filesArray.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;

    this.submit();
});

</script>

</body>
</html>

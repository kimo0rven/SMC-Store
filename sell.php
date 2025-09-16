<?php
session_start();
require './includes/db_connection.php';

$categQuery = "SELECT * FROM categories";
$stmt = $pdo->prepare($categQuery);
$stmt->execute();

$categories = $stmt->fetchAll();

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell | Michaelite Store</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/component.css">

    <style>
        
    </style>
</head>
<body style="gap:2rem">
<header>
    <?php include './public/components/header.php' ?>
</header>

<main>
    <div class="sell-page-container">
        <div class="sell-page-upload-page">
            <div class="upload-container" id="upload-container">
                <img src="./public/assets/images/icons/image_upload_icon.png" alt="">
                <p>Click or drag images here<span><br>(Up to 6 photos)</span></p>
                <input type="file" id="file-input" accept="image/*" multiple hidden>
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

                <div class="sell-page-details-dropdown" id="sell-page-details-subcategory-dropdown" style="display:none;">
                    <div class="sell-page-details-dropdown-selected">Select Subcategory</div>
                    <div class="sell-page-details-dropdown-list">
                        <input type="text" placeholder="Search..." class="sell-page-details-dropdown-search">
                        <ul></ul>
                    </div>
                </div>
            </div>

            <div>
                <input placeholder="Listing Title" type="text">
            </div>
            
            <span>About the item</span>
            <div id="select-test">
                <span>condition</span>
                <input type="radio" id="age1" name="age" value="30">
                <label for="age1">0 - 30</label><br>
                <input type="radio" id="age2" name="age" value="60">
                <label for="age2">31 - 60</label><br>  
                <input type="radio" id="age3" name="age" value="100">
                <label for="age3">61 - 100</label><br>
            </div>

            <div>
                <span>price</span>
                <input type="number" name="" id="">
            </div>
            
            <div>
                <span>description</span>
                <textarea name="" id=""></textarea>
            </div>

            

        </div>
    </div>
</main>


<footer>
    <?php include './public/components/footer.php'  ?>
</footer>

<script>
    const uploadContainer = document.getElementById('upload-container');
    const fileInput = document.getElementById('file-input');
    const preview = document.getElementById('preview');

    let filesArray = [];

    uploadContainer.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFiles);

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

    function handleFiles(e) {
        const newFiles = Array.from(e.target.files);

        if (filesArray.length + newFiles.length > 6) {
            alert('You can only upload up to 6 images.');
            return;
        }

        newFiles.forEach(file => {
            if (!file.type.startsWith('image/')) return;

            filesArray.push(file);

            const reader = new FileReader();
            reader.onload = () => {
                const item = document.createElement('div');
                item.classList.add('preview-item');
                item.draggable = true;

                const img = document.createElement('img');
                img.src = reader.result;

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

                addDragAndDrop(item);
                updateCoverLabel();
            };
            reader.readAsDataURL(file);
        });

        fileInput.value = '';
    }

    // Drag & Drop Reordering
    let draggedItem = null;

    function addDragAndDrop(item) {
        item.addEventListener('dragstart', () => {
            draggedItem = item;
            item.classList.add('dragging');
        });

        item.addEventListener('dragend', () => {
            draggedItem.classList.remove('dragging');
            draggedItem = null;
            updateFilesArrayOrder();
            updateCoverLabel();
        });

        item.addEventListener('dragover', e => {
            e.preventDefault();
            const draggingOver = e.target.closest('.preview-item');
            if (draggingOver && draggingOver !== draggedItem) {
                const items = Array.from(preview.children);
                const draggedIndex = items.indexOf(draggedItem);
                const overIndex = items.indexOf(draggingOver);
                if (draggedIndex < overIndex) {
                    preview.insertBefore(draggedItem, draggingOver.nextSibling);
                } else {
                    preview.insertBefore(draggedItem, draggingOver);
                }
            }
        });
    }

    function updateFilesArrayOrder() {
        const items = Array.from(preview.children);
        const newOrder = [];
        items.forEach(item => {
            const imgSrc = item.querySelector('img').src;
            const file = filesArray.find(f => {
                return URL.createObjectURL(f) === imgSrc || f.previewSrc === imgSrc;
            });
            if (file) newOrder.push(file);
        });
        filesArray = newOrder;
    }


    function updateCoverLabel() {
        // Remove old labels
        preview.querySelectorAll('.cover-label').forEach(label => label.remove());

        const firstItem = preview.querySelector('.preview-item');
        if (firstItem) {
            const label = document.createElement('div');
            label.classList.add('cover-label');
            label.textContent = 'Cover';
            firstItem.appendChild(label);
        }
    }

    function initSellPageDetailsDropdown(dropdownId, hiddenInputId, onSelect) {
        const dropdown = document.getElementById(dropdownId);
        const selected = dropdown.querySelector('.sell-page-details-dropdown-selected');
        const listContainer = dropdown.querySelector('.sell-page-details-dropdown-list');
        const searchInput = dropdown.querySelector('.sell-page-details-dropdown-search');
        const list = dropdown.querySelector('ul');
        const hiddenInput = document.getElementById(hiddenInputId);

        // Toggle dropdown
        selected.addEventListener('click', () => {
            const isOpen = listContainer.style.display === 'flex';
            document.querySelectorAll('.sell-page-details-dropdown-list').forEach(dl => dl.style.display = 'none');
            listContainer.style.display = isOpen ? 'none' : 'flex';
            searchInput.value = '';
            filterList('');
            searchInput.focus();
        });

        // Filter list
        function filterList(term) {
            Array.from(list.children).forEach(li => {
                li.style.display = li.textContent.toLowerCase().includes(term.toLowerCase()) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', () => {
            filterList(searchInput.value);
        });

        // Select item
        list.addEventListener('click', e => {
            if (e.target.tagName === 'LI') {
                selected.textContent = e.target.textContent;
                selected.dataset.value = e.target.dataset.value;
                hiddenInput.value = e.target.dataset.value;
                listContainer.style.display = 'none';
                if (onSelect) onSelect(e.target.dataset.value, e.target.textContent);
            }
        });

        // Close on outside click
        document.addEventListener('click', e => {
            if (!dropdown.contains(e.target)) {
                listContainer.style.display = 'none';
            }
        });
    }

// Initialize Category dropdown
    initSellPageDetailsDropdown(
        'sell-page-details-category-dropdown',
        'sell-page-details-category-input',
        (categoryId) => {
            const subDropdown = document.getElementById('sell-page-details-subcategory-dropdown');
            const subList = subDropdown.querySelector('ul');
            const subSelected = subDropdown.querySelector('.sell-page-details-dropdown-selected');
            const subHiddenInput = document.getElementById('sell-page-details-subcategory-input');

            // Reset subcategory state
            subList.innerHTML = '';
            subSelected.textContent = 'Select Subcategory';
            subHiddenInput.value = '';
            subDropdown.style.display = 'none';

            if (!categoryId) return;

            // Fetch subcategories from PHP
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

                        // Only init once — avoid stacking event listeners
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

</script>





</body>
</html>

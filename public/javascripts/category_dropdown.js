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

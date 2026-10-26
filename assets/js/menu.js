
const searchInput = document.getElementById("menu-search");
const categorySelect = document.getElementById("menu-category");
const menuItems = document.querySelectorAll(".data-menu > .menu");
const emptyMessage = document.getElementById("menu-empty");

function filterMenu() {
    const searchText = searchInput.value
        .trim()
        .toLowerCase();

    const selectedCategory = categorySelect.value;

    let visibleCount = 0;

    menuItems.forEach(function (item) {
        const menuName = item
            .querySelector("article p:first-of-type")
            .textContent
            .trim()
            .toLowerCase();

        const itemCategory = item.dataset.category;

        const matchesSearch = menuName.includes(searchText);

        const matchesCategory =
            selectedCategory === "Semua" ||
            itemCategory === selectedCategory;

        if (matchesSearch && matchesCategory) {
            item.hidden = false;
            visibleCount++;
        } else {
            item.hidden = true;
        }
    });

    emptyMessage.hidden = visibleCount !== 0;
}

searchInput.addEventListener("input", filterMenu);
categorySelect.addEventListener("change", filterMenu);

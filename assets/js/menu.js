
const searchInput = document.getElementById("menu-search");
const categorySelect = document.getElementById("menu-category");
const menuItems = document.querySelectorAll(".data-menu > .menu");
const emptyMessage = document.getElementById("menu-empty");

const cartItems = document.getElementById("cart-items");
const cartEmpty = document.getElementById("cart-empty");
const totalQuantityDisplay = document.getElementById("cart-total-quantity");
const totalPriceDisplay = document.getElementById("cart-total-price");
const checkoutButton = document.getElementById("checkout-button");

const rupiah = new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0
});

// Initialize each menu's quantity controls.
menuItems.forEach(function (item) {
    const nameElement = item.querySelector("article p:first-of-type");
    const control = item.querySelector(".jumlah-barang");

    const menuName = nameElement.textContent.trim();

    item.dataset.quantity = "0";

    // Create the controls using JavaScript.
    const minusButton = document.createElement("button");
    minusButton.type = "button";
    minusButton.className = "quantity-btn minus";
    minusButton.textContent = "−";
    minusButton.disabled = true;
    minusButton.setAttribute(
        "aria-label",
        "Kurangi jumlah " + menuName
    );

    const quantityDisplay = document.createElement("span");
    quantityDisplay.className = "quantity-value";
    quantityDisplay.textContent = "0";
    quantityDisplay.setAttribute("aria-live", "polite");

    const plusButton = document.createElement("button");
    plusButton.type = "button";
    plusButton.className = "quantity-btn plus";
    plusButton.textContent = "+";
    plusButton.setAttribute(
        "aria-label",
        "Tambah jumlah " + menuName
    );

    control.replaceChildren(
        minusButton,
        quantityDisplay,
        plusButton
    );

    plusButton.addEventListener("click", function () {
        const quantity = Number(item.dataset.quantity);
        item.dataset.quantity = String(quantity + 1);

        updateQuantityControls(item);
        updateCart();
    });

    minusButton.addEventListener("click", function () {
        const quantity = Number(item.dataset.quantity);

        if (quantity > 0) {
            item.dataset.quantity = String(quantity - 1);

            updateQuantityControls(item);
            updateCart();
        }
    });
});

// Update one menu card.
function updateQuantityControls(item) {
    const quantity = Number(item.dataset.quantity);

    item.querySelector(".quantity-value").textContent = quantity;
    item.querySelector(".minus").disabled = quantity === 0;
}

// Update the search and category filters.
function filterMenu() {
    const searchText = searchInput.value.trim().toLowerCase();
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

        item.hidden = !(matchesSearch && matchesCategory);

        if (!item.hidden) {
            visibleCount++;
        }
    });

    emptyMessage.hidden = visibleCount !== 0;
}

// Recalculate and render the shopping cart.
function updateCart() {
    cartItems.replaceChildren();

    let totalQuantity = 0;
    let totalPrice = 0;

    menuItems.forEach(function (item) {
        const quantity = Number(item.dataset.quantity);

        if (quantity === 0) {
            return;
        }

        const name = item
            .querySelector("article p:first-of-type")
            .textContent
            .trim();

        const price = Number(item.dataset.price);
        const subtotal = price * quantity;

        totalQuantity += quantity;
        totalPrice += subtotal;

        const listItem = document.createElement("li");
        listItem.className = "cart-item";

        const info = document.createElement("div");
        info.className = "cart-item-info";

        const nameText = document.createElement("div");
        nameText.className = "cart-item-name";
        nameText.textContent = name;

        const detail = document.createElement("div");
        detail.className = "cart-item-detail";
        detail.textContent =
            quantity + " × " + rupiah.format(price);

        const subtotalText = document.createElement("strong");
        subtotalText.className = "cart-item-subtotal";
        subtotalText.textContent = rupiah.format(subtotal);

        info.append(nameText, detail);
        listItem.append(info, subtotalText);
        cartItems.append(listItem);
    });

    cartEmpty.hidden = totalQuantity > 0;

    totalQuantityDisplay.textContent =
        totalQuantity + (totalQuantity === 1 ? " item" : " item");

    totalPriceDisplay.textContent = rupiah.format(totalPrice);

    checkoutButton.disabled = totalQuantity === 0;
}

// Listen for changes in the filters.
searchInput.addEventListener("input", filterMenu);
categorySelect.addEventListener("change", filterMenu);

// Initial state.
filterMenu();
updateCart();

const confirmationSection =
    document.getElementById("order-confirmation");

const reviewItems =
    document.getElementById("order-review-items");

const reviewTotal =
    document.getElementById("order-review-total");

const orderForm =
    document.getElementById("order-form");

const orderResult =
    document.getElementById("order-result");

const backToMenuButton =
    document.getElementById("back-to-menu");

function renderOrderReview() {
    reviewItems.replaceChildren();

    let total = 0;

    menuItems.forEach(function (item) {
        const quantity = Number(item.dataset.quantity);

        if (quantity === 0) {
            return;
        }

        const name = item
            .querySelector("article p:first-of-type")
            .textContent
            .trim();

        const price = Number(item.dataset.price);
        const subtotal = price * quantity;

        total += subtotal;

        const listItem = document.createElement("li");
        listItem.textContent =
            name + " — " + quantity + " × " +
            rupiah.format(price) + " = " +
            rupiah.format(subtotal);

        reviewItems.append(listItem);
    });

    reviewTotal.textContent = rupiah.format(total);
}

// Open the confirmation section.
checkoutButton.addEventListener("click", function () {
    if (checkoutButton.disabled) {
        return;
    }

    orderResult.hidden = true;
    orderResult.textContent = "";

    confirmationSection.hidden = false;
    renderOrderReview();

    confirmationSection.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
});

// Return to the menu.
backToMenuButton.addEventListener("click", function () {
    confirmationSection.hidden = true;

    document.querySelector(".menu-tools").scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
});

// Validate and simulate order confirmation.
orderForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const customerName =
        document.getElementById("customer-name").value.trim();

    const orderType =
        document.getElementById("order-type").value;

    if (!customerName || Number(totalQuantityDisplay.textContent.split(" ")[0]) === 0) {
        orderResult.textContent =
            "Nama pemesan wajib diisi dan pesanan harus memiliki item.";
        orderResult.hidden = false;
        return;
    }

    const message =
        "Konfirmasi demo berhasil untuk " + customerName +
        ". Jenis pesanan: " + orderType +
        ". Pesanan ini belum dikirim ke restoran karena backend belum tersedia.";

    orderResult.textContent = message;
    orderResult.hidden = false;

    orderResult.scrollIntoView({
        behavior: "smooth",
        block: "nearest"
    });
});

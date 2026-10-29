
// ── Guard: must have a seat before browsing the menu ──────────────────────
// (requireSeat() is defined in session.js which is loaded before this file)
if (!requireSeat()) {
    throw new Error("No seat selected — redirecting to Kursi.html");
}

// ── DOM references ─────────────────────────────────────────────────────────

const searchInput    = document.getElementById("menu-search");
const categorySelect = document.getElementById("menu-category");
const menuItems      = document.querySelectorAll(".data-menu > .menu");
const emptyMessage   = document.getElementById("menu-empty");

const cartItemsList         = document.getElementById("cart-items");
const cartEmpty             = document.getElementById("cart-empty");
const totalQuantityDisplay  = document.getElementById("cart-total-quantity");
const totalPriceDisplay     = document.getElementById("cart-total-price");
const checkoutButton        = document.getElementById("checkout-button");

const rupiah = new Intl.NumberFormat("id-ID", {
    style:               "currency",
    currency:            "IDR",
    maximumFractionDigits: 0
});

// ── Seat info banner ───────────────────────────────────────────────────────

(function injectSeatBanner() {
    const seat = getSelectedSeat();
    if (!seat) return;

    const banner = document.createElement("div");
    banner.className = "seat-banner";
    banner.setAttribute("aria-label", "Informasi kursi yang dipilih");

    Object.assign(banner.style, {
        background:    "#fefce8",
        border:        "1px solid #ca8a04",
        borderRadius:  "8px",
        padding:       "0.6rem 1.2rem",
        marginBottom:  "1rem",
        fontSize:      "0.9rem",
        color:         "#92400e",
        display:       "flex",
        alignItems:    "center",
        gap:           "0.5rem"
    });

    banner.innerHTML =
        "🪑 <strong>Meja " + seat.tableId + "</strong>" +
        " &nbsp;·&nbsp; " + seat.guests + " orang" +
        " &nbsp;·&nbsp; " + (seat.date || "-") +
        " &nbsp;·&nbsp; " + (seat.time || "-") +
        " &nbsp;<a href='/Kursi.html' style='margin-left:auto;color:#b45309;" +
        "font-size:0.8rem;text-decoration:underline;'>Ganti meja</a>";

    // Insert right after the welcoming-info block.
    const welcomeInfo = document.querySelector(".welcoming-info");
    if (welcomeInfo && welcomeInfo.parentNode) {
        welcomeInfo.parentNode.insertBefore(banner, welcomeInfo.nextSibling);
    } else {
        document.body.prepend(banner);
    }
})();

// ── Quantity controls & cart persistence ──────────────────────────────────

// Load previously saved cart from session.
const savedCart = getCart(); // [{name, price, quantity}]

function getSavedQuantity(menuName) {
    const found = savedCart.find(function (item) {
        return item.name === menuName;
    });
    return found ? found.quantity : 0;
}

// Initialize each menu card.
menuItems.forEach(function (item) {
    const nameElement = item.querySelector("article p:first-of-type");
    const control     = item.querySelector(".jumlah-barang");

    const menuName = nameElement.textContent.trim();

    // Restore quantity from session.
    const savedQty = getSavedQuantity(menuName);
    item.dataset.quantity = String(savedQty);

    // Build controls.
    const minusButton = document.createElement("button");
    minusButton.type      = "button";
    minusButton.className = "quantity-btn minus";
    minusButton.textContent = "−";
    minusButton.disabled  = savedQty === 0;
    minusButton.setAttribute("aria-label", "Kurangi jumlah " + menuName);

    const quantityDisplay = document.createElement("span");
    quantityDisplay.className   = "quantity-value";
    quantityDisplay.textContent = String(savedQty);
    quantityDisplay.setAttribute("aria-live", "polite");

    const plusButton = document.createElement("button");
    plusButton.type      = "button";
    plusButton.className = "quantity-btn plus";
    plusButton.textContent = "+";
    plusButton.setAttribute("aria-label", "Tambah jumlah " + menuName);

    control.replaceChildren(minusButton, quantityDisplay, plusButton);

    plusButton.addEventListener("click", function () {
        const qty = Number(item.dataset.quantity);
        item.dataset.quantity = String(qty + 1);
        updateQuantityControls(item);
        updateCart();
    });

    minusButton.addEventListener("click", function () {
        const qty = Number(item.dataset.quantity);
        if (qty > 0) {
            item.dataset.quantity = String(qty - 1);
            updateQuantityControls(item);
            updateCart();
        }
    });
});

// ── Update one menu card ───────────────────────────────────────────────────

function updateQuantityControls(item) {
    const qty = Number(item.dataset.quantity);
    item.querySelector(".quantity-value").textContent = qty;
    item.querySelector(".minus").disabled = qty === 0;
}

// ── Filter ─────────────────────────────────────────────────────────────────

function filterMenu() {
    const searchText       = searchInput.value.trim().toLowerCase();
    const selectedCategory = categorySelect.value;

    let visibleCount = 0;

    menuItems.forEach(function (item) {
        const menuName = item
            .querySelector("article p:first-of-type")
            .textContent
            .trim()
            .toLowerCase();

        const itemCategory = item.dataset.category;

        const matchesSearch   = menuName.includes(searchText);
        const matchesCategory =
            selectedCategory === "Semua" || itemCategory === selectedCategory;

        item.hidden = !(matchesSearch && matchesCategory);
        if (!item.hidden) visibleCount++;
    });

    emptyMessage.hidden = visibleCount !== 0;
}

// ── Cart ───────────────────────────────────────────────────────────────────

function buildCartArray() {
    const cart = [];
    menuItems.forEach(function (item) {
        const qty = Number(item.dataset.quantity);
        if (qty === 0) return;

        cart.push({
            name:     item.querySelector("article p:first-of-type").textContent.trim(),
            price:    Number(item.dataset.price),
            quantity: qty
        });
    });
    return cart;
}

function updateCart() {
    cartItemsList.replaceChildren();

    let totalQty   = 0;
    let totalPrice = 0;

    menuItems.forEach(function (item) {
        const qty = Number(item.dataset.quantity);
        if (qty === 0) return;

        const name     = item.querySelector("article p:first-of-type").textContent.trim();
        const price    = Number(item.dataset.price);
        const subtotal = price * qty;

        totalQty   += qty;
        totalPrice += subtotal;

        const listItem    = document.createElement("li");
        listItem.className = "cart-item";

        const info = document.createElement("div");
        info.className = "cart-item-info";

        const nameText = document.createElement("div");
        nameText.className   = "cart-item-name";
        nameText.textContent = name;

        const detail = document.createElement("div");
        detail.className   = "cart-item-detail";
        detail.textContent = qty + " × " + rupiah.format(price);

        const subtotalText = document.createElement("strong");
        subtotalText.className   = "cart-item-subtotal";
        subtotalText.textContent = rupiah.format(subtotal);

        info.append(nameText, detail);
        listItem.append(info, subtotalText);
        cartItemsList.append(listItem);
    });

    cartEmpty.hidden = totalQty > 0;

    totalQuantityDisplay.textContent =
        totalQty + (totalQty === 1 ? " item" : " item");
    totalPriceDisplay.textContent = rupiah.format(totalPrice);

    checkoutButton.disabled = totalQty === 0;

    // Persist cart to session.
    updateSession({ cart: buildCartArray() });
}

// ── Filter listeners ───────────────────────────────────────────────────────

searchInput.addEventListener("input", filterMenu);
categorySelect.addEventListener("change", filterMenu);

// ── Checkout & confirmation ────────────────────────────────────────────────

const confirmationSection = document.getElementById("order-confirmation");
const reviewItems         = document.getElementById("order-review-items");
const reviewTotal         = document.getElementById("order-review-total");
const orderForm           = document.getElementById("order-form");
const orderResult         = document.getElementById("order-result");
const backToMenuButton    = document.getElementById("back-to-menu");

function renderOrderReview() {
    reviewItems.replaceChildren();
    let total = 0;

    menuItems.forEach(function (item) {
        const qty = Number(item.dataset.quantity);
        if (qty === 0) return;

        const name     = item.querySelector("article p:first-of-type").textContent.trim();
        const price    = Number(item.dataset.price);
        const subtotal = price * qty;
        total += subtotal;

        const li = document.createElement("li");
        li.textContent =
            name + " — " + qty + " × " +
            rupiah.format(price) + " = " + rupiah.format(subtotal);
        reviewItems.append(li);
    });

    reviewTotal.textContent = rupiah.format(total);

    // Pre-fill customer name from profile if available.
    const user = getUser();
    const nameField = document.getElementById("customer-name");
    if (nameField && user && user.nama && !nameField.value) {
        nameField.value = user.nama;
    }
}

// Open the confirmation section.
checkoutButton.addEventListener("click", function () {
    if (checkoutButton.disabled) return;

    // Guard 1: seat required (already checked on load, but double-check).
    if (!requireSeat()) return;

    // Guard 2: profile must be complete before finalising.
    if (!requireProfile()) return;

    orderResult.hidden    = true;
    orderResult.textContent = "";

    confirmationSection.hidden = false;
    renderOrderReview();

    confirmationSection.scrollIntoView({ behavior: "smooth", block: "start" });
});

// Return to the menu.
backToMenuButton.addEventListener("click", function () {
    confirmationSection.hidden = true;
    document.querySelector(".menu-tools").scrollIntoView({
        behavior: "smooth",
        block:    "start"
    });
});

// Validate and simulate order confirmation.
orderForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const customerName = document.getElementById("customer-name").value.trim();
    const orderType    = document.getElementById("order-type").value;
    const orderNotes   = document.getElementById("order-notes").value.trim();

    const totalQtyStr = totalQuantityDisplay.textContent;
    const totalQty    = Number(totalQtyStr.split(" ")[0]);

    if (!customerName || totalQty === 0) {
        orderResult.textContent =
            "Nama pemesan wajib diisi dan pesanan harus memiliki item.";
        orderResult.hidden = false;
        return;
    }

    const seat = getSelectedSeat();

    const orderSummary = {
        customerName: customerName,
        orderType:    orderType,
        orderNotes:   orderNotes,
        items:        buildCartArray(),
        seat:         seat,
        orderedAt:    new Date().toISOString()
    };

    // Save last order to session.
    updateSession({ lastOrder: orderSummary, cart: [] });

    // Reset quantities in the DOM.
    menuItems.forEach(function (item) {
        item.dataset.quantity = "0";
        updateQuantityControls(item);
    });
    updateCart();

    const msg =
        "✔ Pesanan berhasil dikonfirmasi untuk " + customerName +
        ". Jenis pesanan: " + orderType +
        ". Meja: " + (seat ? seat.tableId : "-") + ".";

    orderResult.textContent = msg;
    orderResult.hidden      = false;
    confirmationSection.hidden = true;

    orderResult.scrollIntoView({ behavior: "smooth", block: "nearest" });
});

// ── Initial render ─────────────────────────────────────────────────────────

filterMenu();
updateCart();

// Show any pending flash message (e.g., redirected from Dashboard).
showFlashMessage();

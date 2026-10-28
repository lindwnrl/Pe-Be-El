
const tables = document.querySelectorAll(".table.available");

const guestInput = document.getElementById("jumlah-orang");
const dateInput = document.getElementById("tanggal");
const timeInput = document.getElementById("waktu");

const selectedTableText = document.getElementById("selected-table");
const summaryGuests = document.getElementById("summary-guests");
const summaryDate = document.getElementById("summary-date");
const summaryTime = document.getElementById("summary-time");

const confirmButton = document.getElementById("confirm-reservation");
const message = document.getElementById("reservation-message");

let selectedTable = null;

// Select a table when the customer clicks it.
tables.forEach(function (table) {
    table.addEventListener("click", function () {
        // Remove the previous selection.
        tables.forEach(function (item) {
            item.classList.remove("selected");
            item.setAttribute("aria-pressed", "false");
        });

        // Select the clicked table.
        table.classList.add("selected");
        table.setAttribute("aria-pressed", "true");

        selectedTable = {
            id: table.dataset.table,
            capacity: Number(table.dataset.capacity)
        };

        updateSummary();
    });
});

// Update the reservation summary.
function updateSummary() {
    summaryGuests.textContent = guestInput.value + " orang";

    if (selectedTable) {
        selectedTableText.textContent =
            selectedTable.id + " (" + selectedTable.capacity + " kursi)";
    } else {
        selectedTableText.textContent = "Belum dipilih";
    }

    summaryDate.textContent = dateInput.value || "Belum dipilih";
    summaryTime.textContent = timeInput.value || "Belum dipilih";

    updateConfirmationButton();
}

// Enable confirmation only when the form is complete.
function updateConfirmationButton() {
    const guests = Number(guestInput.value);

    const detailsComplete =
        selectedTable !== null &&
        dateInput.value !== "" &&
        timeInput.value !== "";

    const capacityEnough =
        selectedTable !== null &&
        selectedTable.capacity >= guests;

    confirmButton.disabled = !(detailsComplete && capacityEnough);

    if (selectedTable && !capacityEnough) {
        message.textContent =
            "Kapasitas meja tidak mencukupi jumlah orang.";
    } else {
        message.textContent = "";
    }
}

// Update the summary when reservation details change.
guestInput.addEventListener("change", updateSummary);
dateInput.addEventListener("change", updateSummary);
timeInput.addEventListener("change", updateSummary);

// Initial state.
// Prevent selecting a date in the past.
function setMinimumDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day = String(today.getDate()).padStart(2, "0");

    dateInput.min = `${year}-${month}-${day}`;
}

setMinimumDate();

// Initial state.
tables.forEach(function (table) {
    table.setAttribute("aria-pressed", "false");
});

updateSummary();

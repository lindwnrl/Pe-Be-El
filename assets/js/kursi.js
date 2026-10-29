
// ── DOM references ─────────────────────────────────────────────────────────

const tables = document.querySelectorAll(".table.available");

const guestInput = document.getElementById("jumlah-orang");
const dateInput  = document.getElementById("tanggal");
const timeInput  = document.getElementById("waktu");

const selectedTableText = document.getElementById("selected-table");
const summaryGuests     = document.getElementById("summary-guests");
const summaryDate       = document.getElementById("summary-date");
const summaryTime       = document.getElementById("summary-time");

const confirmButton = document.getElementById("confirm-reservation");
const message       = document.getElementById("reservation-message");

// ── State ──────────────────────────────────────────────────────────────────

let selectedTable = null;

// ── Restore previous seat selection from session ───────────────────────────

(function restoreFromSession() {
    const seat = getSelectedSeat();
    if (!seat) return;

    // Restore form inputs.
    if (seat.guests)  guestInput.value = seat.guests;
    if (seat.date)    dateInput.value  = seat.date;
    if (seat.time)    timeInput.value  = seat.time;

    // Restore the highlighted table button.
    tables.forEach(function (table) {
        if (table.dataset.table === seat.tableId) {
            table.classList.add("selected");
            table.setAttribute("aria-pressed", "true");
            selectedTable = {
                id:       seat.tableId,
                capacity: seat.capacity
            };
        } else {
            table.setAttribute("aria-pressed", "false");
        }
    });
})();

// ── Table selection ────────────────────────────────────────────────────────

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
            id:       table.dataset.table,
            capacity: Number(table.dataset.capacity)
        };

        updateSummary();
    });
});

// ── Reservation summary ────────────────────────────────────────────────────

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

// ── Confirm reservation → save to session ─────────────────────────────────

confirmButton.addEventListener("click", function () {
    if (!selectedTable) return;

    const seatData = {
        tableId:  selectedTable.id,
        capacity: selectedTable.capacity,
        guests:   Number(guestInput.value),
        date:     dateInput.value,
        time:     timeInput.value
    };

    // Persist to session.
    updateSession({ selectedSeat: seatData });

    // Visual feedback.
    message.textContent =
        "✔ Meja " + selectedTable.id + " berhasil dipesan! " +
        "Anda sekarang dapat memesan makanan.";
    message.style.color = "#15803d";

    confirmButton.textContent = "Reservasi Tersimpan ✔";
    confirmButton.style.background = "#15803d";
});

// ── Form change listeners ──────────────────────────────────────────────────

guestInput.addEventListener("change", updateSummary);
dateInput.addEventListener("change",  updateSummary);
timeInput.addEventListener("change",  updateSummary);

// ── Initial setup ──────────────────────────────────────────────────────────

// Prevent selecting a date in the past.
function setMinimumDate() {
    const today = new Date();
    const year  = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day   = String(today.getDate()).padStart(2, "0");
    dateInput.min = `${year}-${month}-${day}`;
}

setMinimumDate();

// Set initial aria state for any tables not already marked by restore.
tables.forEach(function (table) {
    if (!table.getAttribute("aria-pressed")) {
        table.setAttribute("aria-pressed", "false");
    }
});

updateSummary();

// Show flash message (e.g. redirected here because no seat was selected).
showFlashMessage();


// ── DOM references ─────────────────────────────────────────────────────────

const tables = document.querySelectorAll(".table");

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

// The table this browser just booked. Reloading occupancy must not clear the
// session seat for it — the table is occupied precisely because of us.
let ownSeatId = null;

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
        // Occupied tables are disabled, but guard anyway.
        if (table.classList.contains("unavailable")) return;

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

// ── Occupancy ─────────────────────────────────────────────────────────────

/** Paint one table as taken, or back to available. */
function setOccupied(table, occupied, label) {
    table.classList.toggle("unavailable", occupied);
    table.classList.toggle("available", !occupied);
    table.disabled = occupied;

    let note = table.querySelector("small");

    if (occupied) {
        if (!note) {
            note = document.createElement("small");
            table.appendChild(note);
        }
        note.textContent = label || "Terisi";
        table.classList.remove("selected");
        table.setAttribute("aria-pressed", "false");

        if (selectedTable && selectedTable.id === table.dataset.table) {
            selectedTable = null;
            updateSummary();
        }
    } else if (note) {
        note.remove();
    }
}

/** Fetch which tables are currently held and repaint the floor plan. */
function loadOccupancy() {
    return fetch("/api/reservasi.php", { credentials: "same-origin" })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data || !data.ok) return;

            const taken = data.tables || {};

            tables.forEach(function (table) {
                const id = table.dataset.table;
                setOccupied(table, Boolean(taken[id]), taken[id] ? "Terisi" : null);
            });

            // A seat restored from session may now be gone — unless this
            // browser is the one holding it.
            const seat = getSelectedSeat();
            if (seat && taken[seat.tableId] && seat.tableId !== ownSeatId) {
                updateSession({ selectedSeat: null });
            }

            updateSummary();
        })
        .catch(function () {
            // Leave the floor plan as rendered; booking will surface any conflict.
        });
}

// ── Confirm reservation → persist, then remember it locally ───────────────

confirmButton.addEventListener("click", function () {
    if (!selectedTable) return;

    // Guests cannot confirm a reservation.
    if (!requireLoginModal()) return;

    const seatData = {
        tableId:  selectedTable.id,
        capacity: selectedTable.capacity,
        guests:   Number(guestInput.value),
        date:     dateInput.value,
        time:     timeInput.value
    };

    confirmButton.disabled = true;
    message.textContent = "Menyimpan reservasi...";

    fetch("/api/reservasi.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({
            meja:         seatData.tableId,
            nama:         (getUser() || {}).nama || "",
            jumlah_orang: seatData.guests,
            tanggal:      seatData.date,
            waktu:        seatData.time
        })
    })
        .then(function (res) {
            if (res.status === 401) {
                window.location.href = "/Login.php";
                return null;
            }
            return res.json().then(function (data) {
                return { status: res.status, data: data };
            });
        })
        .then(function (result) {
            if (!result) return;

            if (!result.data || !result.data.ok) {
                message.textContent = (result.data && result.data.error) ||
                    "Reservasi gagal disimpan.";
                confirmButton.classList.remove("is-confirmed");
                confirmButton.textContent = "Konfirmasi Reservasi";
                loadOccupancy();
                updateConfirmationButton();
                return;
            }

            // Persist to session so ordering and the menu banner keep working.
            ownSeatId = seatData.tableId;
            updateSession({ selectedSeat: seatData });

            confirmButton.textContent = "Reservasi Tersimpan ✔";
            confirmButton.classList.add("is-confirmed");

            // Repaint first: updateSummary() clears the message element.
            loadOccupancy().then(function () {
                message.textContent =
                    "✔ Meja " + seatData.tableId + " berhasil dipesan. " +
                    "Meja tetap terisi sampai admin mengaktifkannya kembali.";
            });
        })
        .catch(function () {
            message.textContent = "Reservasi gagal disimpan. Silakan coba lagi.";
            updateConfirmationButton();
        });
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

loadOccupancy();

updateSummary();

// Show flash message (e.g. redirected here because no seat was selected).
showFlashMessage();

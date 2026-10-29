
// ── Profile picture preview ────────────────────────────────────────────────

const pictureInput   = document.getElementById("picture-input");
const profilePicture = document.getElementById("profile-picture");

pictureInput.addEventListener("change", function () {
    const file = pictureInput.files[0];
    if (!file) return;

    if (!file.type.startsWith("image/")) {
        alert("Silakan pilih file gambar.");
        pictureInput.value = "";
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const dataURL = e.target.result;
        profilePicture.src = dataURL;

        // Persist picture to session.
        updateSession({ user: { profilePicture: dataURL } });
    };
    reader.readAsDataURL(file);
});

// ── Restore profile data from session ─────────────────────────────────────

(function restoreProfile() {
    const user = getUser();

    const fields = {
        username:      "username",
        nama:          "nama",
        email:         "email",
        no_hp:         "no_hp",
        tanggal_lahir: "tanggal-lahir"
    };

    for (const [key, id] of Object.entries(fields)) {
        const el = document.getElementById(id);
        if (el && user[key]) el.value = user[key];
    }

    // Restore radio buttons.
    if (user.jenis_kelamin) {
        const radio = document.getElementById(user.jenis_kelamin);
        if (radio) radio.checked = true;
    }

    // Restore profile picture.
    if (user.profilePicture && profilePicture) {
        profilePicture.src = user.profilePicture;
    }
})();

// ── Save profile on form submit ────────────────────────────────────────────

const profileForm = document.querySelector(".form-profile");

profileForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const username      = document.getElementById("username").value.trim();
    const nama          = document.getElementById("nama").value.trim();
    const email         = document.getElementById("email").value.trim();
    const no_hp         = document.getElementById("no_hp").value.trim();
    const tanggal_lahir = document.getElementById("tanggal-lahir").value;

    const genderRadio = document.querySelector(
        "input[name='jenis_kelamin']:checked"
    );
    const jenis_kelamin = genderRadio ? genderRadio.value : "";

    // Mandatory fields: username, nama, email.
    const isComplete = !!(username && nama && email);

    updateSession({
        user: {
            isLoggedIn:     true,
            username:       username,
            nama:           nama,
            email:          email,
            no_hp:          no_hp,
            jenis_kelamin:  jenis_kelamin,
            tanggal_lahir:  tanggal_lahir
        },
        isProfileComplete: isComplete
    });

    if (isComplete) {
        showSuccessMessage(
            "Profil berhasil disimpan! Anda sekarang dapat melanjutkan pemesanan."
        );
    } else {
        showSuccessMessage(
            "Data tersimpan, tetapi Username, Nama, dan Email wajib diisi untuk memesan."
        );
    }
});

// ── Success notification ───────────────────────────────────────────────────

function showSuccessMessage(text) {
    let existing = document.getElementById("profile-save-msg");
    if (!existing) {
        existing = document.createElement("p");
        existing.id = "profile-save-msg";
        existing.setAttribute("role", "status");
        existing.setAttribute("aria-live", "polite");
        Object.assign(existing.style, {
            marginTop:  "0.75rem",
            padding:    "0.6rem 1rem",
            background: "#dcfce7",
            border:     "1px solid #16a34a",
            borderRadius: "6px",
            color:      "#166534",
            fontSize:   "0.9rem"
        });

        const submitBtn = profileForm.querySelector("button[type='submit']");
        if (submitBtn && submitBtn.parentNode) {
            submitBtn.parentNode.insertAdjacentElement("afterend", existing);
        } else {
            profileForm.appendChild(existing);
        }
    }

    existing.textContent = text;

    // Auto-clear after 6 seconds.
    setTimeout(function () {
        if (existing.parentNode) existing.textContent = "";
    }, 6000);
}

// Show any pending flash (e.g., redirected here from Menu.html).
showFlashMessage();

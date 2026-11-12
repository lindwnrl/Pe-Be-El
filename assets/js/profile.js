// ── Profile picture preview ────────────────────────────────────────────────

const pictureInput   = document.getElementById("picture-input");
const profilePicture = document.getElementById("profile-picture");

if (pictureInput) {
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
}

// ── Restore profile data from session (quick first render) ──────────────────

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

    if (user.jenis_kelamin) {
        const radio = document.getElementById(user.jenis_kelamin);
        if (radio) radio.checked = true;
    }

    if (user.profilePicture && profilePicture) {
        profilePicture.src = user.profilePicture;
    }
})();

// ── Fetch fresh profile from server on load ────────────────────────────────

(function loadProfileFromServer() {
    fetch("/api/profile.php", { method: "GET", credentials: "same-origin" })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data || !data.ok || !data.profile) return;

            const p = data.profile;

            const fields = {
                username:      "username",
                nama:          "nama",
                email:         "email",
                no_hp:         "no_hp",
                tanggal_lahir: "tanggal-lahir"
            };

            for (const [key, id] of Object.entries(fields)) {
                const el = document.getElementById(id);
                if (el && p[key]) el.value = p[key];
            }

            if (p.jenis_kelamin) {
                const radio = document.getElementById(p.jenis_kelamin);
                if (radio) radio.checked = true;
            }

            const isComplete = !!(p.nama && p.no_hp && p.email && p.jenis_kelamin && p.tanggal_lahir);
            updateSession({
                user: {
                    isLoggedIn:    true,
                    username:      getUser().username,
                    nama:          p.nama || "",
                    email:         p.email || "",
                    no_hp:         p.no_hp || "",
                    jenis_kelamin: p.jenis_kelamin || "",
                    tanggal_lahir: p.tanggal_lahir || ""
                },
                isProfileComplete: isComplete
            });
        })
        .catch(function () {
            // If the server call fails, the session values already rendered are kept.
        });
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

    // Mandatory fields include tanggal_lahir now.
    const isComplete = !!(username && nama && email && no_hp && jenis_kelamin && tanggal_lahir);

    if (!tanggal_lahir) {
        showErrorMessage("Tanggal lahir wajib diisi.");
        document.getElementById("tanggal-lahir").focus();
        return;
    }

    if (!isComplete) {
        showErrorMessage("Semua data wajib diisi, termasuk tanggal lahir.");
        return;
    }

    const payload = {
        nama:          nama,
        email:         email,
        no_hp:         no_hp,
        jenis_kelamin: jenis_kelamin,
        tanggal_lahir: tanggal_lahir
    };

    fetch("/api/profile.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data || !data.ok) {
                showErrorMessage((data && data.error) || "Gagal menyimpan profil.");
                return;
            }

            updateSession({
                user: {
                    isLoggedIn:    true,
                    username:      username,
                    nama:          nama,
                    email:         email,
                    no_hp:         no_hp,
                    jenis_kelamin: jenis_kelamin,
                    tanggal_lahir: tanggal_lahir
                },
                isProfileComplete: true
            });

            var destination = "/Dashboard.php";
            var destinationName = "Dashboard";

            if (isAdmin()) {
                destination = "/Admin.php";
                destinationName = "Admin";
            } else if (isOwner()) {
                destination = "/Owner.php";
                destinationName = "Owner";
            }

            showSuccessMessage(
                "Profil berhasil disimpan! Mengarahkan ke " + destinationName + "..."
            );

            setTimeout(function () {
                window.location.href = destination;
            }, 1500);
        })
        .catch(function () {
            showErrorMessage("Gagal menyimpan profil. Silakan coba lagi.");
        });
});

// ── Success notification ───────────────────────────────────────────────────

function showMessageAlert(id, role, className, text, autoClear) {
    let existing = document.getElementById(id);
    if (!existing) {
        existing = document.createElement("p");
        existing.id = id;
        existing.setAttribute("role", role);
        existing.setAttribute("aria-live", "polite");
        profileForm.appendChild(existing);
    }

    existing.className = className;
    existing.textContent = text;

    if (autoClear) {
        setTimeout(function () {
            if (existing.parentNode) existing.textContent = "";
        }, 6000);
    }
}

function showSuccessMessage(text) {
    showMessageAlert("profile-save-msg", "status", "form-alert form-alert--ok", text, true);
}

function showErrorMessage(text) {
    showMessageAlert("profile-error-msg", "alert", "form-alert form-alert--error", text, false);
}

// Show any pending flash (e.g., redirected here from Menu.php).
showFlashMessage();

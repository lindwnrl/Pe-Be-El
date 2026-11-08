<?php require_once __DIR__ . '/includes/session-boot.php'; start_session(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Nina Rasa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <main class="auth-page">
        <aside class="auth-brand">
            <a href="/Dashboard.php" class="logo">
                <span class="logo__mark" aria-hidden="true">N</span>
                <span class="logo__text">
                    <strong>NINA RASA</strong>
                    <small>NIMIE &middot; POS &amp; INVENTORY</small>
                </span>
            </a>

            <h1>Daftar sekali,<span>pesan kapan saja.</span></h1>
            <p>Buat akun untuk memesan makanan, memesan meja, dan menyimpan profil
               Anda di Nina Rasa.</p>

            <div class="auth-brand__card">
                <small>NIMIE &middot; Nina Rasa</small>
                <strong>Pesan mudah.<br><em>Makan lebih seru.</em></strong>
                <p>Data Anda tersimpan dan siap dipakai lagi.</p>
            </div>
        </aside>

        <div class="auth-panel">
            <section class="auth-card">
                <h1 class="auth-title">Buat akun</h1>
                <p class="auth-sub">Isi data di bawah untuk mulai memesan</p>

                <form id="register-form" novalidate>
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="username">
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                    </div>

                    <div class="field">
                        <label for="confirm-password">Konfirmasi Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Ulangi password" required autocomplete="new-password">
                    </div>

                    <p id="register-error" class="form-alert form-alert--error" role="alert" aria-live="polite"></p>

                    <button type="submit" class="button">Daftar</button>

                    <p class="auth-alt">
                        Sudah punya akun? <a href="/Login.php">Masuk di sini</a>
                    </p>
                </form>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <p class="site-footer__brand">Nina Rasa</p>
            <p>Bakso &amp; Mie Ayam · Buka 10.00–20.00</p>
            <nav aria-label="Navigasi footer">
                <a href="/Dashboard.php">Home</a>
                <a href="/Menu.php">Menu</a>
                <a href="/Kursi.php">Kursi</a>
            </nav>
        </div>
    </footer>

    <script src="/assets/js/session.js"></script>
    <script>
        document.getElementById("register-form").addEventListener("submit", function (e) {
            e.preventDefault();

            const username  = document.getElementById("username").value.trim();
            const password  = document.getElementById("password").value;
            const confirm   = document.getElementById("confirm-password").value;
            const errorEl   = document.getElementById("register-error");

            // Validate fields.
            if (!username || !password) {
                errorEl.textContent = "Username dan password wajib diisi.";
                errorEl.style.display = "block";
                return;
            }

            if (password !== confirm) {
                errorEl.textContent = "Password dan konfirmasi password tidak cocok.";
                errorEl.style.display = "block";
                return;
            }

            if (password.length < 6) {
                errorEl.textContent = "Password minimal 6 karakter.";
                errorEl.style.display = "block";
                return;
            }

            // Persist to the `akun` table through the PHP endpoint.
            errorEl.style.display = "none";

            fetch("/api/register.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ username: username, password: password, confirm_password: confirm })
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data || !data.ok) {
                        errorEl.textContent = (data && data.error) || "Pendaftaran gagal. Silakan coba lagi.";
                        errorEl.style.display = "block";
                        return;
                    }

                    sessionStorage.setItem(
                        "ninaRasaFlash",
                        "Registrasi berhasil! Silakan masuk dengan akun Anda."
                    );
                    window.location.href = "/Login.php";
                })
                .catch(function () {
                    errorEl.textContent = "Pendaftaran gagal. Silakan coba lagi.";
                    errorEl.style.display = "block";
                });
        });
    </script>
</body>
</html>

<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Nina Rasa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bevan:ital@0;1&display=swap" rel="stylesheet">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <div class="auth-card__art">
                <img src="/assets/documentasi/1f5515c814af4b017c4106159f9214a88b93d314.png" alt="Nina Rasa">
            </div>

            <div class="auth-card__body">
                <h1 class="auth-title">Daftar</h1>
                <p class="auth-sub">Buat akun untuk memesan dan memesan meja.</p>

                <form id="register-form" novalidate>
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" required autocomplete="username">
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required autocomplete="new-password">
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
            </div>
        </section>
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

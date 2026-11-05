<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Nina Rasa</title>
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
                <h1 class="auth-title">Masuk</h1>
                <p class="auth-sub">Silakan masuk untuk melanjutkan pesanan Anda.</p>

                <form id="login-form" novalidate>
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" required autocomplete="username">
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                    </div>

                    <p id="login-error" class="form-alert form-alert--error" role="alert" aria-live="polite"></p>

                    <button type="submit" class="button">Masuk</button>

                    <p class="auth-alt">
                        Belum ada akun? <a href="/Register.php">Daftar yuk!</a>
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
        // Show any flash from a previous redirect.
        showFlashMessage();

        // If already logged in, skip login page.
        if (isLoggedIn()) {
            window.location.href = "/Dashboard.php";
        }

        document.getElementById("login-form").addEventListener("submit", function (e) {
            e.preventDefault();

            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value;
            const errorEl  = document.getElementById("login-error");

            if (!username || !password) {
                errorEl.textContent = "Username dan password wajib diisi.";
                errorEl.style.display = "block";
                return;
            }

            // ── Real auth: verify credentials against the `akun` table ───
            errorEl.style.display = "none";

            fetch("/api/login.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ username: username, password: password })
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data || !data.ok) {
                        errorEl.textContent = (data && data.error) || "Username atau password salah.";
                        errorEl.style.display = "block";
                        return;
                    }

                    // Mark user as logged in in session (same shape as before).
                    const userData = data.user || {};
                    const isAdmin = userData.role === "admin";
                    const isComplete = !!(
                        userData.nama &&
                        userData.no_hp &&
                        userData.email &&
                        userData.jenis_kelamin &&
                        userData.tanggal_lahir
                    );

                    updateSession({
                        user: {
                            isLoggedIn:    true,
                            username:      username,
                            role:          userData.role || "",
                            nama:          userData.nama || "",
                            email:         userData.email || "",
                            no_hp:         userData.no_hp || "",
                            jenis_kelamin: userData.jenis_kelamin || "",
                            tanggal_lahir: userData.tanggal_lahir || ""
                        },
                        // Admins and customers follow the same profile rule.
                        isProfileComplete: isComplete
                    });

                    // Admins land on the console, but only once their profile is complete.
                    if (isAdmin) {
                        window.location.href = isComplete ? "/Admin.php" : "/Profile.php";
                    } else {
                        window.location.href = "/Profile.php";
                    }
                })
                .catch(function () {
                    errorEl.textContent = "Login gagal. Silakan coba lagi.";
                    errorEl.style.display = "block";
                });
        });
    </script>
</body>
</html>

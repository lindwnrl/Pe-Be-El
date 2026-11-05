<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bevan:ital@0;1&display=swap" rel="stylesheet">
    <title>Profil — Nina Rasa</title>
</head>
<body>
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="/Dashboard.php" class="logo">
                <img src="/assets/documentasi/1f5515c814af4b017c4106159f9214a88b93d314.png" alt="Nina Rasa — Home">
            </a>
            <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-nav" aria-label="Buka navigasi">☰</button>
            <nav class="site-nav" aria-label="Navigasi utama">
                <ul id="site-nav"><!-- filled by nav.js --></ul>
            </nav>
        </div>
    </header>

    <main class="container profile-page">
        <div class="card3">
            <form class="form-profile" novalidate>
                <h1>Profil saya</h1>

                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>

                <div class="field">
                    <label for="nama">Nama</label>
                    <input type="text" name="nama" id="nama" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="field">
                    <label for="no_hp">No. Telepon</label>
                    <input type="text" name="no_hp" id="no_hp" inputmode="tel">
                </div>

                <div class="field">
                    <span class="field-label" id="jenis_kelamin_label">Jenis Kelamin</span>
                    <span class="radio-group" role="radiogroup" aria-labelledby="jenis_kelamin_label">
                        <label for="laki-laki">
                            <input type="radio" name="jenis_kelamin" id="laki-laki" value="laki-laki">
                            Laki-laki
                        </label>
                        <label for="perempuan">
                            <input type="radio" name="jenis_kelamin" id="perempuan" value="perempuan">
                            Perempuan
                        </label>
                    </span>
                </div>

                <div class="field">
                    <label for="tanggal-lahir">Tanggal lahir <span class="req">*</span></label>
                    <span class="tanggal-field">
                        <input type="date" name="tanggal-lahir" id="tanggal-lahir" required>
                        <button type="submit" id="simpan-profil" class="button">Simpan Profil</button>
                    </span>
                </div>
            </form>

            <div class="photo-profile">
                <img id="profile-picture" src="/assets/documentasi/jokowi ganteng.png" alt="Foto profil">
                <input type="file" id="picture-input" accept="image/*" hidden>
                <label for="picture-input" class="button button--ghost">Ubah Gambar</label>
            </div>
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

    <!-- session.js MUST be loaded before profile.js -->
    <script src="/assets/js/session.js"></script>
    <script src="/assets/js/nav.js"></script>
    <script>
        // Guard: must be logged in to view Profile.
        if (!isLoggedIn()) {
            window.location.href = '/Login.php';
        }

        // Show any flash messages (e.g., redirected from another page).
        showFlashMessage();

        // Guard the Menu nav link: require seat for logged-in users with complete profile.
        document.getElementById('nav-menu').addEventListener('click', function (e) {
            e.preventDefault();
            if (!isLoggedIn()) {
                window.location.href = '/Menu.php';
                return;
            }
            if (!hasSeat()) {
                sessionStorage.setItem(
                    'ninaRasaFlash',
                    'Silakan pilih kursi/meja terlebih dahulu sebelum memesan makanan.'
                );
                window.location.href = '/Kursi.php';
            } else {
                window.location.href = '/Menu.php';
            }
        });
    </script>
    <script src="/assets/js/profile.js"></script>
</body>
</html>

<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <title>Profile</title>
</head>
<body>
    <header>
        <a href="/Dashboard.php" class="logo">
            <img src="/assets/documentasi/1f5515c814af4b017c4106159f9214a88b93d314.png" alt="Homepage">
        </a>
        <nav>
            <ul id="site-nav"><!-- filled by nav.js --></ul>
        </nav>
    </header>
        <div class="card3">
            <form class="form-profile" novalidate>
                <h1>Profile saya</h1>
                <p>
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </p>
                <p>
                    <label for="nama">Nama</label>
                    <input type="text" name="nama" id="nama" required>
                </p>
                <p>
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" required>
                </p>
                <p>
                    <label for="no_hp">No.Telepon</label>
                    <input type="text" name="no_hp" id="no_hp">
                </p>
                
                <p>
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <span>
                        <input type="radio" name="jenis_kelamin" id="laki-laki" value="laki-laki">
                        <label for="laki-laki">Laki-laki</label>
                        
                        <input type="radio" name="jenis_kelamin" id="perempuan" value="perempuan">
                        <label for="perempuan">Perempuan</label>
                    </span>    
                </p>
                
                <p class="tanggal-row">
                    <label for="tanggal-lahir">Tanggal lahir <span style="color:#b91c1c">*</span></label>
                    <span class="tanggal-field">
                        <input type="date" name="tanggal-lahir" id="tanggal-lahir" required>
                        <button type="submit" id="simpan-profil">Simpan Profil</button>
                    </span>
                </p>
            </form>
            <div class="photo-profile">
                <img id="profile-picture" src="/assets/documentasi/jokowi ganteng.png" alt="">
                <input type="file" id="picture-input" accept="image/*" hidden>
                <label for="picture-input" class="button">Ubah Gambar</label>
            </div>
        </div>

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

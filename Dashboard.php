<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <title>Dashboard</title>
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
    
    <main>
        <section class="home-card">
            <div class="card1">
                <p>Selamat Datang</p>
                <h1>WEBSITE NINA RASA</h1>
                <span class="home-dots" aria-hidden="true"></span>
            </div>
            <img src="/assets/documentasi/1f5515c814af4b017c4106159f9214a88b93d314.png" alt="">
        </section>

            
            <div class="profile">
                <section class="card2">
                    <img src="/assets/documentasi/dcaea2ebd2c04f20be30ca4fe5a84067e019ba6f.jpg" alt="">
                </section>
                <div class="info">
                    <h1>Profil Singkat Nina Rasa</h1>
                    <p>Kedai Nina Rasa adalah usaha kuliner spesialis bakso dan mie ayam lezat
                        yang kini hadir di berbagai lokasi cabang. Menghadirkan olahan 
                        daging sapi pilihan, racikan mie kenyal, serta kuah kaldu gurih dengan resep
                        khas, Kedai Nina Rasa berkomitmen untuk selalu menjaga kualitas rasa,
                        kebersihan, dan pelayanan terbaik secara konsisten di setiap cabangnya.</p>
                    </div>
            </div>

            <section>

                <div class="best-menu">
                    <!-- Menu links are handled by JS to enforce seat-guard for logged-in users -->
                    <a href="#" class="menu-link">
                        <article>
                            <img src="/assets/documentasi/image1.png" alt="">
                            <h3>Mie Ayam Mozarella</h3>
                        </article>
                    </a>
                    <a href="#" class="menu-link">
                        <article>
                            <img src="/assets/documentasi/image2.png" alt="">
                            <h3>Mie Ayam Ceker</h3>
                        </article>
                    </a>
                    <a href="#" class="menu-link">
                        <article>
                            <img src="/assets/documentasi/image3.png" alt="">
                            <h3>Bakso Solo Campur</h3>
                        </article>
                    </a>
                </div>   
            </section>
        
    </main>

    <script src="/assets/js/session.js"></script>
    <script src="/assets/js/nav.js"></script>
    <script>
        // Profile gate: logged-in users with incomplete profile must go to Profile.php.
        enforceProfileGate();

        // Show any flash messages (e.g. after logout).
        showFlashMessage();

        // ── Menu navigation guard ──────────────────────────────────────────
        /**
         * For logged-in users: enforce seat selection before going to Menu.
         * For guests: navigate directly to Menu.php (they can browse but not order).
         */
        function handleMenuNavigation(event) {
            event.preventDefault();

            if (!isLoggedIn()) {
                // Guests go directly to Menu.php — no seat required.
                window.location.href = '/Menu.php';
                return;
            }

            // Logged-in users must have a seat first.
            if (!hasSeat()) {
                sessionStorage.setItem(
                    'ninaRasaFlash',
                    'Silakan pilih kursi/meja terlebih dahulu sebelum memesan makanan.'
                );
                window.location.href = '/Kursi.php';
                return;
            }

            window.location.href = '/Menu.php';
        }

        // Apply guard to the nav "Menu" link.
        const navMenuLink = document.getElementById('nav-menu');
        if (navMenuLink) {
            navMenuLink.addEventListener('click', handleMenuNavigation);
        }

        // Apply guard to the food card links in the best-menu section.
        document.querySelectorAll('.menu-link').forEach(function (link) {
            link.addEventListener('click', handleMenuNavigation);
        });
    </script>
</body>
</html>

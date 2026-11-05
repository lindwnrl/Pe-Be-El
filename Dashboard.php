<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/includes/auth.php';

// The landing page is public. Signed-in users belong inside the app.
if (auth_user() !== null) {
    header('Location: /Menu.php', true, 302);
    exit;
}

// Single place to edit the address shown on the landing page.
const NINA_RASA_ALAMAT = 'Alamat belum diisi — Jl. Contoh No. 1, Malang, Jawa Timur';
const NINA_RASA_KOTA   = 'Malang';
const NINA_RASA_TELEPON = ''; // rendered only when non-empty

$mapsQuery = rawurlencode(NINA_RASA_ALAMAT . ', ' . NINA_RASA_KOTA);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nina Rasa — bakso dan mie ayam rumahan di Malang. Buka setiap hari 10.00–20.00.">
    <meta name="theme-color" content="#faf7f3">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bevan:ital@0;1&display=swap" rel="stylesheet">
    <title>Nina Rasa — Bakso &amp; Mie Ayam, Malang</title>
</head>
<body data-page="landing">
    <a class="skip-link" href="#menu">Langsung ke menu</a>

    <header class="site-header">
        <div class="container site-header__inner">
            <a href="/Dashboard.php" class="logo">
                <img src="/assets/documentasi/1f5515c814af4b017c4106159f9214a88b93d314.png" alt="Nina Rasa — beranda">
            </a>
            <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-nav" aria-label="Buka navigasi">☰</button>
            <nav class="site-nav" aria-label="Navigasi utama">
                <ul id="site-nav"><!-- filled by nav.js --></ul>
            </nav>
        </div>
    </header>

    <main>

        <!-- ══ 1. Home ══════════════════════════════════════════════════════ -->
        <section id="home" class="landing-section landing-section--home" aria-labelledby="home-title">
            <div class="container hero-layout">
                <div class="hero-copy">
                    <p class="hero-eyebrow"><span aria-hidden="true"></span>Bakso &amp; Mie Ayam · Sejak dulu</p>

                    <h1 id="home-title">Rasa hangat, seperti <em>di rumah.</em></h1>

                    <p class="hero-description">
                        Bakso dan mie ayam yang dimasak segar setiap hari,
                        dengan resep rumahan dan bahan pilihan.
                    </p>

                    <div class="hero-actions">
                        <a class="button" href="#menu">Lihat menu</a>
                        <a class="button button--ghost" href="#location">Kunjungi kami</a>
                    </div>

                    <p class="hero-meta">
                        <span>Buka setiap hari</span>
                        <span class="hero-meta__dot" aria-hidden="true">·</span>
                        <span>10.00–20.00</span>
                        <span class="hero-meta__dot" aria-hidden="true">·</span>
                        <span><?= htmlspecialchars(NINA_RASA_KOTA) ?></span>
                    </p>
                </div>

                <div class="hero-visual">
                    <span class="hero-decoration" aria-hidden="true"></span>

                    <div class="hero-plate" id="hero-plate">
                        <span class="plate-rim" aria-hidden="true"></span>
                        <img class="hero-plate__img"
                             src="/assets/documentasi/1f5515c814af4b017c4106159f9214a88b93d314.png"
                             alt="Logo Nina Rasa">
                        <span class="hero-fallback" aria-hidden="true">
                            <span class="food food--1">🍜</span>
                            <span class="food food--2">🍲</span>
                            <span class="food food--3">🥟</span>
                            <span class="food food--4">🌶️</span>
                        </span>
                    </div>

                    <span class="hero-sticker" aria-hidden="true">
                        <span>Sejak</span>
                        <strong>Nina Rasa</strong>
                    </span>
                </div>
            </div>
        </section>

        <!-- ══ 2. Profile ═══════════════════════════════════════════════════ -->
        <section id="profile" class="landing-section landing-section--profile" aria-labelledby="profile-title">
            <div class="container">
                <p class="section-eyebrow">Profil</p>

                <div class="profile">
                    <section class="card2">
                        <img src="/assets/documentasi/dcaea2ebd2c04f20be30ca4fe5a84067e019ba6f.jpg"
                             alt="Suasana Kedai Nina Rasa">
                    </section>
                    <div class="info">
                        <h2 id="profile-title">Profil Singkat Nina Rasa</h2>
                        <p>Kedai Nina Rasa adalah usaha kuliner spesialis bakso dan mie ayam lezat
                            yang kini hadir di berbagai lokasi cabang. Menghadirkan olahan
                            daging sapi pilihan, racikan mie kenyal, serta kuah kaldu gurih dengan resep
                            khas, Kedai Nina Rasa berkomitmen untuk selalu menjaga kualitas rasa,
                            kebersihan, dan pelayanan terbaik secara konsisten di setiap cabangnya.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ 3. Menu ══════════════════════════════════════════════════════ -->
        <section id="menu" class="landing-section landing-section--menu" aria-labelledby="menu-title">
            <div class="container">
                <div class="rail-head">
                    <div>
                        <p class="section-eyebrow">Menu</p>
                        <h2 id="menu-title">Semua menu Nina Rasa</h2>
                    </div>

                    <div class="rail-controls">
                        <button type="button" class="rail-nav" data-dir="-1" aria-label="Menu sebelumnya">‹</button>
                        <button type="button" class="rail-nav" data-dir="1" aria-label="Menu berikutnya">›</button>
                    </div>
                </div>

                <div class="menu-rail" id="menu-rail" tabindex="0" role="region"
                     aria-label="Daftar menu Nina Rasa">
                    <article class="rail-card rail-card--skeleton" aria-hidden="true">
                        <span class="skeleton skeleton--art"></span>
                        <span class="skeleton skeleton--line"></span>
                        <span class="skeleton skeleton--line skeleton--short"></span>
                    </article>
                    <article class="rail-card rail-card--skeleton" aria-hidden="true">
                        <span class="skeleton skeleton--art"></span>
                        <span class="skeleton skeleton--line"></span>
                        <span class="skeleton skeleton--line skeleton--short"></span>
                    </article>
                    <article class="rail-card rail-card--skeleton" aria-hidden="true">
                        <span class="skeleton skeleton--art"></span>
                        <span class="skeleton skeleton--line"></span>
                        <span class="skeleton skeleton--line skeleton--short"></span>
                    </article>
                </div>

                <div class="rail-progress" aria-hidden="true">
                    <span class="rail-progress__bar" id="rail-progress-bar"></span>
                </div>

                <p class="rail-foot">
                    <a class="button" href="/Menu.php">Lihat menu lengkap &amp; pesan</a>
                </p>
            </div>
        </section>

        <!-- ══ 4. Location ══════════════════════════════════════════════════ -->
        <section id="location" class="landing-section landing-section--location" aria-labelledby="location-title">
            <div class="container location-layout">
                <div class="location-copy">
                    <p class="section-eyebrow">Lokasi</p>
                    <h2 id="location-title">Makan enak, ada <em>dekat.</em></h2>
                    <p class="location-intro">
                        Mampir untuk menikmati hidangan hangat langsung dari dapur kami di
                        <?= htmlspecialchars(NINA_RASA_KOTA) ?>.
                    </p>

                    <div class="location-card">
                        <div class="location-row">
                            <span class="location-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"></path>
                                    <circle cx="12" cy="10" r="2.6"></circle>
                                </svg>
                            </span>
                            <div>
                                <h3>Alamat</h3>
                                <p><?= htmlspecialchars(NINA_RASA_ALAMAT) ?></p>
                            </div>
                        </div>

                        <div class="location-row">
                            <span class="location-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="M12 7v5l3.2 2"></path>
                                </svg>
                            </span>
                            <div>
                                <h3>Jam buka</h3>
                                <p>Setiap hari · 10.00–20.00 WIB</p>
                            </div>
                        </div>

                        <a class="button button--ghost location-button"
                           href="https://www.google.com/maps/search/?api=1&amp;query=<?= $mapsQuery ?>"
                           target="_blank" rel="noopener noreferrer">Buka di Google Maps</a>
                    </div>
                </div>

                <div class="location-map" aria-hidden="true">
                    <span class="location-pin">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.6A2.6 2.6 0 1 1 12 6.4a2.6 2.6 0 0 1 0 5.2z"></path>
                        </svg>
                    </span>
                    <span class="location-map__label"><?= htmlspecialchars(NINA_RASA_KOTA) ?></span>
                </div>
            </div>
        </section>

    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <p class="site-footer__brand">Nina Rasa</p>
            <p>Bakso &amp; Mie Ayam · Buka 10.00–20.00</p>
            <nav aria-label="Navigasi footer">
                <a href="#home">Home</a>
                <a href="#profile">Profile</a>
                <a href="#menu">Menu</a>
                <a href="#location">Location</a>
                <a href="/Login.php">Login</a>
            </nav>
        </div>
    </footer>

    <script src="/assets/js/session.js"></script>
    <script>
        // Fallback guard: PHP session may have expired while sessionStorage still
        // believes the visitor is signed in.
        if (isLoggedIn()) { window.location.replace('/Menu.php'); }
    </script>
    <script src="/assets/js/nav.js"></script>
    <script>
        (function () {
            'use strict';

            /* ── Hero image fallback ─────────────────────────────────────── */

            var plate = document.getElementById('hero-plate');
            var plateImg = plate ? plate.querySelector('.hero-plate__img') : null;

            if (plateImg) {
                plateImg.addEventListener('error', function () {
                    plate.classList.add('image-failed');
                });
            }

            /* ── Menu rail ───────────────────────────────────────────────── */

            var rail      = document.getElementById('menu-rail');
            var railBar   = document.getElementById('rail-progress-bar');
            var arrows    = document.querySelectorAll('.rail-nav');
            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            var rupiah = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            });

            if (!rail) return;

            function cardWidth() {
                var card = rail.querySelector('.rail-card');
                if (!card) return 264;
                var styles = window.getComputedStyle(rail);
                var gap = parseFloat(styles.columnGap || styles.gap) || 16;
                return card.getBoundingClientRect().width + gap;
            }

            function scrollRail(direction) {
                rail.scrollBy({
                    left: direction * cardWidth(),
                    behavior: reduceMotion ? 'auto' : 'smooth'
                });
            }

            function updateRailChrome() {
                var maxScroll = rail.scrollWidth - rail.clientWidth;
                var atStart = rail.scrollLeft <= 1;
                var atEnd   = rail.scrollLeft >= maxScroll - 1;

                arrows.forEach(function (btn) {
                    var dir = Number(btn.dataset.dir);
                    btn.disabled = dir < 0 ? atStart : atEnd;
                });

                if (railBar) {
                    var ratio = maxScroll <= 0 ? 1 : rail.clientWidth / rail.scrollWidth;
                    var travel = maxScroll <= 0 ? 0 : rail.scrollLeft / maxScroll;
                    railBar.style.width = (ratio * 100) + '%';
                    railBar.style.transform =
                        'translateX(' + (travel * (100 / ratio - 100)) + '%)';
                }
            }

            arrows.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    scrollRail(Number(btn.dataset.dir));
                });
            });

            rail.addEventListener('scroll', updateRailChrome, { passive: true });
            window.addEventListener('resize', updateRailChrome);

            rail.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    scrollRail(1);
                } else if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    scrollRail(-1);
                }
            });

            /* ── Card rendering ──────────────────────────────────────────── */

            function buildCard(item) {
                var card = document.createElement('article');
                card.className = 'rail-card';
                card.dataset.menuId = String(item.menu_id);

                if (!item.tersedia) card.classList.add('is-soldout');

                var media = document.createElement('div');
                media.className = 'rail-card__media';

                if (item.gambar) {
                    var img = document.createElement('img');
                    img.src = item.gambar;
                    img.alt = item.nama_menu;
                    img.loading = 'lazy';
                    media.appendChild(img);
                }

                var badge = document.createElement('span');
                badge.className = 'status-badge ' + (item.tersedia ? 'tersedia' : 'habis');
                badge.textContent = item.tersedia ? 'Tersedia' : 'Habis';
                media.appendChild(badge);

                var body = document.createElement('div');
                body.className = 'rail-card__body';

                var category = document.createElement('p');
                category.className = 'rail-card__category';
                category.textContent = item.kategori || 'Menu';

                var name = document.createElement('h3');
                name.className = 'rail-card__name';
                name.textContent = item.nama_menu;

                var foot = document.createElement('div');
                foot.className = 'rail-card__foot';

                var price = document.createElement('p');
                price.className = 'rail-card__price';
                price.textContent = rupiah.format(item.harga);

                var order = document.createElement('button');
                order.type = 'button';
                order.className = 'button button--sm';
                order.textContent = 'Pesan';

                if (!item.tersedia) {
                    order.disabled = true;
                    order.textContent = 'Habis';
                } else {
                    order.addEventListener('click', function () {
                        requireLoginModal();
                    });
                }

                foot.appendChild(price);
                foot.appendChild(order);

                body.appendChild(category);
                body.appendChild(name);
                body.appendChild(foot);

                card.appendChild(media);
                card.appendChild(body);
                return card;
            }

            function renderItems(items) {
                rail.replaceChildren.apply(rail, items.map(buildCard));
                updateRailChrome();
            }

            function renderError() {
                var card = document.createElement('article');
                card.className = 'rail-card rail-card--message';

                var text = document.createElement('p');
                text.textContent = 'Menu tidak dapat dimuat. Silakan muat ulang halaman.';

                var retry = document.createElement('button');
                retry.type = 'button';
                retry.className = 'button button--ghost button--sm';
                retry.textContent = 'Muat ulang';
                retry.addEventListener('click', loadMenu);

                card.appendChild(text);
                card.appendChild(retry);
                rail.replaceChildren(card);
            }

            function loadMenu() {
                fetch('/api/menu-public.php', { credentials: 'same-origin' })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data || !data.ok || !Array.isArray(data.items)) {
                            renderError();
                            return;
                        }
                        renderItems(data.items);
                    })
                    .catch(function () {
                        renderError();
                    });
            }

            loadMenu();
        })();
    </script>
</body>
</html>

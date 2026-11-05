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
    <title>Pilih Meja — Nina Rasa</title>
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

    <main class="container reservation-page">
        <section class="reservation-intro">
            <h1>Pilih Meja Anda</h1>
            <p>Temukan meja yang nyaman untuk menikmati hidangan bersama orang terdekat.</p>
        </section>

        <div class="reservation-layout">

            <!-- 1. Reservation form -->
            <section class="reservation-form">
                <h2>Detail Reservasi</h2>

                <div class="form-group">
                    <label for="jumlah-orang">Jumlah Orang</label>
                    <select id="jumlah-orang" name="jumlah-orang">
                        <option value="1">1 orang</option>
                        <option value="2" selected>2 orang</option>
                        <option value="3">3 orang</option>
                        <option value="4">4 orang</option>
                        <option value="5">5 orang</option>
                        <option value="6">6 orang</option>
                        <option value="7">7 orang</option>
                        <option value="8">8 orang</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal Reservasi</label>
                    <input type="date" id="tanggal" name="tanggal">
                </div>

                <div class="form-group">
                    <label for="waktu">Waktu Reservasi</label>
                    <select id="waktu" name="waktu">
                        <option value="">Pilih waktu</option>
                        <option value="11:00">11:00</option>
                        <option value="12:00">12:00</option>
                        <option value="13:00">13:00</option>
                        <option value="14:00">14:00</option>
                        <option value="18:00">18:00</option>
                        <option value="19:00">19:00</option>
                        <option value="20:00">20:00</option>
                    </select>
                </div>
            </section>

            <!-- 2. Floor plan -->
            <section class="floor-plan-section">
                <div class="floor-plan-heading">
                    <h2>Pilih Meja</h2>
                    <p>Tekan meja untuk memilih tempat duduk.</p>
                </div>

                <div class="floor-plan">
                    <div class="entrance">Pintu Masuk</div>

                    <button type="button" class="table available" data-table="MO1" data-capacity="4">
                        <strong>MO1</strong>
                        <span>4 kursi</span>
                    </button>

                    <button type="button" class="table available" data-table="MO2" data-capacity="4">
                        <strong>MO2</strong>
                        <span>4 kursi</span>
                    </button>

                    <button type="button" class="table available" data-table="MO3" data-capacity="4">
                        <strong>MO3</strong>
                        <span>4 kursi</span>
                    </button>

                    <button type="button" class="table available" data-table="MO4" data-capacity="6">
                        <strong>MO4</strong>
                        <span>6 kursi</span>
                    </button>

                    <button type="button" class="table available" data-table="MO5" data-capacity="6">
                        <strong>MO5</strong>
                        <span>6 kursi</span>
                    </button>

                    <button type="button" class="table unavailable" data-table="MO6" data-capacity="2" disabled>
                        <strong>MO6</strong>
                        <span>2 kursi</span>
                        <small>Terisi</small>
                    </button>

                    <button type="button" class="table available" data-table="MO7" data-capacity="2">
                        <strong>MO7</strong>
                        <span>2 kursi</span>
                    </button>

                    <button type="button" class="table available" data-table="MO8" data-capacity="4">
                        <strong>MO8</strong>
                        <span>4 kursi</span>
                    </button>

                    <button type="button" class="table available" data-table="MO9" data-capacity="4">
                        <strong>MO9</strong>
                        <span>4 kursi</span>
                    </button>

                    <button type="button" class="table available" data-table="MO10" data-capacity="8">
                        <strong>MO10</strong>
                        <span>8 kursi</span>
                    </button>
                </div>

                <div class="table-legend">
                    <span><span class="legend-dot available"></span> Tersedia</span>
                    <span><span class="legend-dot selected"></span> Dipilih</span>
                    <span><span class="legend-dot unavailable"></span> Tidak tersedia</span>
                </div>
            </section>

            <!-- 3. Reservation summary -->
            <aside class="reservation-summary">
                <h2>Reservasi Anda</h2>

                <div class="summary-content">
                    <p>Meja</p>
                    <h3 id="selected-table">Belum dipilih</h3>

                    <p>Jumlah Orang</p>
                    <h3 id="summary-guests">2 orang</h3>

                    <p>Tanggal</p>
                    <h3 id="summary-date">Belum dipilih</h3>

                    <p>Waktu</p>
                    <h3 id="summary-time">Belum dipilih</h3>
                </div>

                <button type="button" id="confirm-reservation" disabled>
                    Konfirmasi Reservasi
                </button>

                <p id="reservation-message" aria-live="polite"></p>
            </aside>
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
    <script src="/assets/js/nav.js"></script>
    <script>
        // Profile gate: logged-in users with incomplete profile must go to Profile.php.
        enforceProfileGate();
    </script>
    <script src="/assets/js/kursi.js"></script>
</body>
</html>

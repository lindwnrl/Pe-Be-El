<?php
require_once __DIR__ . '/includes/session-boot.php';
require_once __DIR__ . '/includes/auth.php';

start_session();
$isStaff = auth_is_staff();

require_once __DIR__ . '/api/db.php';

$pdo = db();

$menuItems = $pdo->query(
    'SELECT m.menu_id, m.nama_menu, m.harga, m.stok, m.gambar, k.nama_kategori '
    . 'FROM menu m LEFT JOIN kategori_menu k ON k.kategori_id = m.kategori_id '
    . 'WHERE m.aktif = TRUE ORDER BY m.menu_id'
)->fetchAll();

$kategoriList = $pdo->query(
    'SELECT nama_kategori FROM kategori_menu ORDER BY nama_kategori'
)->fetchAll();

$rupiah = static fn (float $value): string => 'Rp ' . number_format($value, 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — Nina Rasa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <a href="/Dashboard.php" class="logo">
                <span class="logo__mark" aria-hidden="true">N</span>
                <span class="logo__text">
                    <strong>NINA RASA</strong>
                    <small>NIMIE &middot; POS &amp; INVENTORY</small>
                </span>
            </a>

            <p class="sidebar__label">Menu utama</p>
            <nav class="sidebar__nav" id="sidebar-nav" aria-label="Navigasi utama">
                <!-- filled by nav.js -->
            </nav>

            <p class="sidebar__foot">NIMIE v1.0 &middot; 2026</p>
        </aside>

        <main class="shell__main">
    <div class="container menu-info">
        <div class="welcoming-info">
            <?php if ($isStaff): ?>
                <p>Mode staf</p>
                <h1>Katalog menu</h1>
                <p>Pratinjau tampilan pelanggan. Stok diatur di halaman admin.</p>
            <?php else: ?>
                <p>Dapur Nina Rasa</p>
                <h1>Silakan pilih menu kesukaanmu</h1>
                <p>Dibuat dengan sepenuh hati</p>
                <span class="status-buka" aria-hidden="true">Buka hari ini · 10.00–20.00</span>
            <?php endif; ?>
        </div>

        <section class="menu-tools" aria-label="Cari dan filter menu">
            <label class="menu-search">
                <svg class="menu-search__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <circle cx="7" cy="7" r="4.5"></circle>
                    <path d="M10.5 10.5 L14 14"></path>
                </svg>
                <input id="menu-search" type="search" placeholder="Cari nama menu..." aria-label="Cari nama menu">
            </label>

            <label class="menu-category">
                <span>Kategori:</span>
                <select id="menu-category">
                    <option value="Semua">Semua</option>
<?php foreach ($kategoriList as $kat): ?>
                    <option value="<?= htmlspecialchars($kat['nama_kategori']) ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
<?php endforeach; ?>
                </select>
            </label>
        </section>

        <p id="menu-empty" hidden>Menu tidak ditemukan.</p>

        <div class="menu-layout">
            <div class="data-menu">
<?php foreach ($menuItems as $item): ?>
<?php
    $gambar = $item['gambar'] !== null && $item['gambar'] !== ''
        ? $item['gambar']
        : '/assets/documentasi/1f5515c814af4b017c4106159f9214a88b93d314.png';
    $nama   = (string) $item['nama_menu'];
    $kategori = (string) ($item['nama_kategori'] ?? '');
?>
                <div class="menu"
                     data-menu-id="<?= (int) $item['menu_id'] ?>"
                     data-category="<?= htmlspecialchars($kategori) ?>"
                     data-price="<?= (int) $item['harga'] ?>">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="<?= htmlspecialchars($gambar) ?>" alt="<?= htmlspecialchars($nama) ?>">
                        </div>
                        <p><?= htmlspecialchars($nama) ?></p>
                        <div class="buy-row">
                            <p><?= $rupiah((float) $item['harga']) ?></p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah <?= htmlspecialchars($nama) ?>" disabled>&minus;</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah <?= htmlspecialchars($nama) ?>">+</button>
                            </div>
                        </div>
                    </article>
                </div>
<?php endforeach; ?>
            </div>

            <section class="cart-summary" aria-labelledby="cart-heading">
                <div class="cart-header">
                    <h2 id="cart-heading">Pesanan Saya</h2>
                    <span id="cart-total-quantity">0 item</span>
                </div>

                <p id="cart-empty">
                    Belum ada menu yang dipilih.
                </p>

                <ul id="cart-items"></ul>

                <div class="cart-footer">
                    <span>Total Pembayaran</span>
                    <strong id="cart-total-price">Rp0</strong>
                </div>

                <button id="checkout-button" type="button" disabled>
                    Lanjutkan Pesanan
                </button>
            </section>
        </div>

        <section id="order-confirmation" class="order-confirmation" hidden>
            <h2>Konfirmasi Pesanan</h2>
            <p class="confirmation-description">
                Periksa pesananmu sebelum melanjutkan.
            </p>

            <div class="order-review">
                <h3>Ringkasan Pesanan</h3>
                <ul id="order-review-items"></ul>

                <div class="order-review-total">
                    <span>Total Pembayaran</span>
                    <strong id="order-review-total">Rp0</strong>
                </div>
            </div>

            <form id="order-form">
                <div class="order-field">
                    <label for="customer-name">Nama Pemesan</label>
                    <input
                        type="text"
                        id="customer-name"
                        name="customerName"
                        placeholder="Masukkan nama kamu"
                        required
                        maxlength="100"
                    >
                </div>

                <div class="order-field">
                    <label for="order-type">Jenis Pesanan</label>
                    <select id="order-type" name="orderType" required>
                        <option value="Dine-in">Makan di tempat</option>
                        <option value="Takeaway">Bawa pulang</option>
                    </select>
                </div>

                <div class="order-field">
                    <label for="order-notes">Catatan (opsional)</label>
                    <textarea
                        id="order-notes"
                        name="orderNotes"
                        placeholder="Contoh: tanpa mie, kuah dipisah"
                        rows="3"
                        maxlength="500"
                    ></textarea>
                </div>

                <div class="order-actions">
                    <button type="button" id="back-to-menu">
                        Kembali ke Menu
                    </button>

                    <button type="submit" id="confirm-order">
                        Konfirmasi Pesanan
                    </button>
                </div>
            </form>

            <p id="order-result" role="status" aria-live="polite" hidden></p>
        </section>
        </main>
    </div>

    <!-- <footer class="site-footer">
        <div class="container site-footer__inner">
            <p class="site-footer__brand">Nina Rasa</p>
            <p>Bakso &amp; Mie Ayam · Buka 10.00–20.00</p>
            <nav aria-label="Navigasi footer">
                <a href="/Dashboard.php">Home</a>
                <a href="/Menu.php">Menu</a>
                <a href="/Kursi.php">Kursi</a>
            </nav>
        </div>
    </footer> -->

    <script src="/assets/js/session.js"></script>
    <script src="/assets/js/nav.js"></script>
    <script src="/assets/js/menu.js"></script>
</body>
</html>

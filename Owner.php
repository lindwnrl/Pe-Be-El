<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

auth_start();
require_owner_page();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Owner — Nina Rasa</title>
</head>
<body data-shell="sidebar">
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
            <nav class="sidebar__nav" id="sidebar-nav" aria-label="Navigasi owner">
                <!-- filled by nav.js -->
            </nav>

            <p class="sidebar__foot">NIMIE v1.0 &middot; 2026</p>
        </aside>

        <main class="shell__main">
            <div class="page-head">
                <div>
                    <h1>Menu &amp; Harga</h1>
                    <p>Kelola katalog dan pantau penjualan harian.</p>
                </div>
                <p class="page-head__meta" id="owner-date">Memuat&hellip;</p>
            </div>

            <div class="report-row">
                <?php require __DIR__ . '/includes/sales-panel.php'; ?>
                <?php require __DIR__ . '/includes/best-sellers-panel.php'; ?>
            </div>

            <section class="panel" aria-labelledby="account-title">
                <div class="panel__head">
                    <div>
                        <h2 id="account-title">Akun staf</h2>
                        <p>Buat akun admin atau owner baru. Pelanggan mendaftar sendiri.</p>
                    </div>
                    <button type="button" id="account-new" class="button button--sm">+ Tambah akun</button>
                </div>

                <form class="menu-form" id="account-form" hidden>
                    <div class="field">
                        <label for="account-username">Username</label>
                        <input type="text" id="account-username" minlength="3" maxlength="50"
                               placeholder="contoh: kasir.malang" required autocomplete="off">
                    </div>

                    <div class="field">
                        <label for="account-password">Password</label>
                        <input type="password" id="account-password" minlength="8"
                               placeholder="Minimal 8 karakter" required autocomplete="new-password">
                    </div>

                    <div class="field">
                        <label for="account-role">Role</label>
                        <select id="account-role" required>
                            <option value="admin">Admin — stok, meja, laporan</option>
                            <option value="owner">Owner — menu, akun, laporan</option>
                        </select>
                    </div>

                    <div class="menu-form__actions">
                        <button type="button" id="account-cancel" class="button button--ghost button--sm">Batal</button>
                        <button type="submit" id="account-submit" class="button button--sm">Buat akun</button>
                    </div>
                </form>

                <p id="account-feedback" class="form-alert" role="status" aria-live="polite"></p>

                <div class="admin-table-wrap">
                    <table class="admin-stock-table">
                        <thead>
                            <tr>
                                <th scope="col">Username</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Role</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="owner-accounts-body">
                            <tr><td colspan="4" class="admin-loading">Memuat akun...</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel" aria-labelledby="menu-manage-title">
                <div class="panel__head">
                    <div>
                        <h2 id="menu-manage-title">Katalog menu</h2>
                        <p>Tambah menu baru, ubah harga, atau hapus dari katalog.</p>
                    </div>
                    <button type="button" id="menu-new" class="button button--sm">+ Tambah menu</button>
                </div>

                <form class="menu-form" id="menu-form" hidden>
                    <input type="hidden" id="menu-id">

                    <div class="field">
                        <label for="menu-nama">Nama menu</label>
                        <input type="text" id="menu-nama" maxlength="100" placeholder="Contoh: Mie Ayam Bakso" required>
                    </div>

                    <div class="field">
                        <label for="menu-kategori">Kategori</label>
                        <select id="menu-kategori" required></select>
                    </div>

                    <div class="field">
                        <label for="menu-harga">Harga (Rp)</label>
                        <input type="number" id="menu-harga" min="0" step="500" placeholder="18000" required>
                    </div>

                    <div class="field">
                        <label for="menu-stok">Stok</label>
                        <input type="number" id="menu-stok" min="0" step="1" value="10" required>
                    </div>

                    <div class="field menu-form__wide">
                        <span class="field-label" id="menu-gambar-label">Gambar menu</span>

                        <div class="dropzone" id="menu-dropzone" role="button" tabindex="0"
                             aria-labelledby="menu-gambar-label">
                            <input type="file" id="menu-gambar-file" accept="image/*" hidden>

                            <div class="dropzone__empty" id="menu-dropzone-empty">
                                <span class="dropzone__icon" aria-hidden="true">⤒</span>
                                <strong>Tarik gambar ke sini</strong>
                                <small>atau klik untuk memilih file · JPG, PNG, WEBP</small>
                            </div>

                            <div class="dropzone__filled" id="menu-dropzone-filled" hidden>
                                <img id="menu-gambar-preview" alt="Pratinjau gambar menu">
                                <div class="dropzone__meta">
                                    <strong id="menu-gambar-name">gambar.jpg</strong>
                                    <small id="menu-gambar-size"></small>
                                </div>
                                <button type="button" id="menu-gambar-clear"
                                        class="button button--ghost button--sm">Hapus</button>
                            </div>
                        </div>

                        <input type="hidden" id="menu-gambar" value="">

                        <details class="dropzone__advanced">
                            <summary>Pakai path atau URL gambar</summary>
                            <input type="text" id="menu-gambar-path" maxlength="255"
                                   placeholder="/assets/documentasi/image1.png atau https://…">
                        </details>
                    </div>

                    <div class="field menu-form__wide">
                        <label for="menu-deskripsi">Deskripsi</label>
                        <input type="text" id="menu-deskripsi" maxlength="255" placeholder="Deskripsi singkat (opsional)">
                    </div>

                    <div class="menu-form__actions">
                        <button type="button" id="menu-cancel" class="button button--ghost button--sm">Batal</button>
                        <button type="submit" id="menu-submit" class="button button--sm">Simpan menu</button>
                    </div>
                </form>

                <p id="menu-feedback" class="form-alert" role="status" aria-live="polite"></p>

                <div class="admin-table-wrap">
                    <table class="admin-stock-table">
                        <thead>
                            <tr>
                                <th scope="col">Gambar</th>
                                <th scope="col">Produk</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Stok</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="owner-menu-body">
                            <tr><td colspan="7" class="admin-loading">Memuat katalog...</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="/assets/js/session.js"></script>
    <script src="/assets/js/nav.js"></script>
    <script src="/assets/js/sales-chart.js"></script>
    <script src="/assets/js/best-sellers.js"></script>
    <script src="/assets/js/owner-accounts.js"></script>
    <script src="/assets/js/owner-menu.js"></script>
    <script>
        (function setDate() {
            var el = document.getElementById('owner-date');
            if (!el) return;
            el.textContent = new Intl.DateTimeFormat('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
            }).format(new Date());
        })();
    </script>
</body>
</html>
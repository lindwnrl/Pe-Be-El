<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_page();

$admin = auth_user();
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
    <title>Admin — Stok Produk</title>
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
            <nav class="sidebar__nav" id="sidebar-nav" aria-label="Navigasi admin">
                <!-- filled by nav.js -->
            </nav>

            <p class="sidebar__foot">NIMIE v1.0 &middot; 2026</p>
        </aside>

        <main class="shell__main">
            <div class="page-head">
                <div>
                    <h1 id="admin-stock-heading">Manajemen Stok</h1>
                    <p>Atur ketersediaan produk jadi yang bisa dipesan pelanggan.</p>
                </div>
                <p class="page-head__meta" id="admin-date">Memuat&hellip;</p>
            </div>

            <div class="report-row">
                <?php require __DIR__ . '/includes/sales-panel.php'; ?>
                <?php require __DIR__ . '/includes/best-sellers-panel.php'; ?>
            </div>

            <section class="panel" aria-labelledby="admin-meja-title">
                <div class="panel__head">
                    <div>
                        <h2 id="admin-meja-title">Meja terisi</h2>
                        <p>Meja tetap terisi sampai diaktifkan kembali di sini.</p>
                    </div>
                    <button type="button" id="admin-reservasi-refresh" class="button button--ghost button--sm">Muat ulang</button>
                </div>

                <p id="admin-reservasi-note" class="form-alert" role="status" aria-live="polite"></p>

                <div class="admin-table-wrap">
                    <table class="admin-stock-table">
                        <thead>
                            <tr>
                                <th scope="col">Meja</th>
                                <th scope="col">Pemesan</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col">Jadwal</th>
                                <th scope="col">Sejak</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="admin-reservasi-body">
                            <tr><td colspan="6" class="admin-loading">Memuat data meja...</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="stat-grid">
                <article class="stat-card">
                    <p class="stat-card__label">Total produk</p>
                    <strong class="stat-card__value" id="stat-total">&mdash;</strong>
                    <span class="stat-card__hint info">Di katalog</span>
                </article>
                <article class="stat-card">
                    <p class="stat-card__label">Tersedia</p>
                    <strong class="stat-card__value" id="stat-ready">&mdash;</strong>
                    <span class="stat-card__hint ok">Bisa dipesan</span>
                </article>
                <article class="stat-card">
                    <p class="stat-card__label">Habis</p>
                    <strong class="stat-card__value" id="stat-empty">&mdash;</strong>
                    <span class="stat-card__hint bad">Perlu diisi ulang</span>
                </article>
            </div>

        <section class="admin-panel" aria-labelledby="admin-stock-heading">
            <div class="admin-panel-head">
                <p id="admin-summary" class="admin-summary" role="status" aria-live="polite">
                    Memuat data stok...
                </p>
                <button type="button" id="admin-refresh" class="button button--ghost button--sm">Muat ulang</button>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-stock-table">
                    <thead>
                        <tr>
                            <th scope="col">Produk</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Stok</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="admin-stock-body">
                        <tr>
                            <td colspan="6" class="admin-loading">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

            <!-- <footer class="site-footer">
                <div class="site-footer__inner">
                    <p class="site-footer__brand">Nina Rasa</p>
                    <p>Bakso &amp; Mie Ayam · Buka 10.00–20.00</p>
                    <nav aria-label="Navigasi footer">
                        <a href="/Menu.php">Menu</a>
                        <a href="/Kursi.php">Kursi</a>
                        <a href="/Admin.php">Stok</a>
                    </nav>
                </div>
            </footer> -->
        </main>
    </div>

    <!-- <footer class="site-footer" hidden>
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
    <script src="/assets/js/sales-chart.js"></script>
    <script src="/assets/js/best-sellers.js"></script>
    <script src="/assets/js/admin-reservasi.js"></script>
    <script>
        // Admins must complete their profile before using the console.
        enforceProfileGate();

        // Show any flash messages (e.g. redirected from Profile.php).
        showFlashMessage();

        // Header date, per the dashboard reference.
        (function setDate() {
            var el = document.getElementById('admin-date');
            if (!el) return;
            el.textContent = new Intl.DateTimeFormat('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
            }).format(new Date());
        })();
    </script>
    <script>
        (function () {
            'use strict';

            const API      = '/api/admin/menu-stock.php';
            const body     = document.getElementById('admin-stock-body');
            const summary  = document.getElementById('admin-summary');
            const refresh  = document.getElementById('admin-refresh');

            const rupiah = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            });

            function handleAuthFailure(response) {
                if (response.status === 401 || response.status === 403) {
                    window.location.href = '/Login.php';
                    return true;
                }
                return false;
            }

            function makeButton(label, className, onClick) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = className;
                btn.textContent = label;
                btn.addEventListener('click', onClick);
                return btn;
            }

            function buildRow(item) {
                const tr = document.createElement('tr');
                tr.dataset.menuId = String(item.menu_id);

                const nameCell = document.createElement('td');
                nameCell.className = 'admin-product-name';
                nameCell.textContent = item.nama_menu;

                const categoryCell = document.createElement('td');
                categoryCell.textContent = item.nama_kategori || '-';

                const priceCell = document.createElement('td');
                priceCell.textContent = rupiah.format(item.harga);

                // Stock: − input +
                const stockCell = document.createElement('td');
                const control   = document.createElement('div');
                control.className = 'stock-control';

                const input = document.createElement('input');
                input.type = 'number';
                input.min = '0';
                input.step = '1';
                input.value = String(item.stok);
                input.className = 'stock-input';
                input.setAttribute('aria-label', 'Stok ' + item.nama_menu);

                const minusBtn = makeButton('−', 'stock-step', function () {
                    const next = Number(input.value || 0) - 1;
                    input.value = String(next < 0 ? 0 : next);
                });
                minusBtn.setAttribute('aria-label', 'Kurangi stok ' + item.nama_menu);

                const plusBtn = makeButton('+', 'stock-step', function () {
                    const next = Number(input.value || 0) + 1;
                    input.value = String(next);
                });
                plusBtn.setAttribute('aria-label', 'Tambah stok ' + item.nama_menu);

                control.append(minusBtn, input, plusBtn);
                stockCell.appendChild(control);

                const statusCell = document.createElement('td');
                const badge = document.createElement('span');
                badge.className = 'status-badge';
                statusCell.appendChild(badge);

                const actionCell = document.createElement('td');
                const feedback   = document.createElement('span');
                feedback.className = 'admin-feedback';
                feedback.setAttribute('role', 'status');
                feedback.setAttribute('aria-live', 'polite');

                const saveBtn = makeButton('Simpan', 'button button--sm', function () {
                    saveStock(item, input, badge, feedback, saveBtn);
                });

                const actions = document.createElement('div');
                actions.className = 'stock-actions';
                actions.append(saveBtn, feedback);
                actionCell.appendChild(actions);

                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        saveStock(item, input, badge, feedback, saveBtn);
                    }
                });

                tr.append(nameCell, categoryCell, priceCell, stockCell, statusCell, actionCell);
                paintStatus(item, badge);
                return tr;
            }

            function paintStatus(item, badge) {
                const habis = item.stok <= 0;
                badge.textContent = habis ? 'Habis' : 'Tersedia';
                badge.className = 'status-badge ' + (habis ? 'habis' : 'tersedia');
            }

            function saveStock(item, input, badge, feedback, saveBtn) {
                const raw = input.value.trim();
                const value = Number(raw);

                if (raw === '' || !Number.isInteger(value)) {
                    feedback.textContent = 'Stok harus berupa angka bulat.';
                    feedback.className = 'admin-feedback error';
                    return;
                }

                if (value < 0) {
                    feedback.textContent = 'Stok tidak boleh negatif.';
                    feedback.className = 'admin-feedback error';
                    return;
                }

                saveBtn.disabled = true;
                feedback.textContent = 'Menyimpan...';
                feedback.className = 'admin-feedback';

                fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ menu_id: item.menu_id, stok: value })
                })
                    .then(function (res) {
                        if (handleAuthFailure(res)) return null;
                        return res.json().then(function (data) {
                            return { status: res.status, data: data };
                        });
                    })
                    .then(function (result) {
                        if (!result) return;

                        if (!result.data || !result.data.ok) {
                            const msg = (result.data && result.data.error)
                                ? result.data.error
                                : 'Gagal menyimpan stok.';
                            feedback.textContent = msg;
                            feedback.className = 'admin-feedback error';
                            return;
                        }

                        item.stok = result.data.item.stok;
                        item.status = result.data.item.status;
                        input.value = String(item.stok);
                        paintStatus(item, badge);
                        feedback.textContent = 'Tersimpan ✓';
                        feedback.className = 'admin-feedback ok';
                        updateSummary();
                    })
                    .catch(function () {
                        feedback.textContent = 'Gagal menyimpan stok. Silakan coba lagi.';
                        feedback.className = 'admin-feedback error';
                    })
                    .then(function () {
                        saveBtn.disabled = false;
                    });
            }

            let items = [];

            const statTotal = document.getElementById('stat-total');
            const statReady = document.getElementById('stat-ready');
            const statEmpty = document.getElementById('stat-empty');

            function updateSummary() {
                const total = items.length;
                const habis = items.filter(function (i) { return i.stok <= 0; }).length;
                summary.textContent =
                    total + ' produk · ' + habis + ' habis · ' +
                    (total - habis) + ' tersedia';

                if (statTotal) statTotal.textContent = String(total);
                if (statReady) statReady.textContent = String(total - habis);
                if (statEmpty) statEmpty.textContent = String(habis);
            }

            function render(data) {
                items = data;
                body.replaceChildren();

                if (!items.length) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 6;
                    td.className = 'admin-loading';
                    td.textContent = 'Belum ada produk.';
                    tr.appendChild(td);
                    body.appendChild(tr);
                    updateSummary();
                    return;
                }

                items.forEach(function (item) {
                    body.appendChild(buildRow(item));
                });

                updateSummary();
            }

            function load() {
                refresh.disabled = true;
                summary.textContent = 'Memuat data stok...';

                fetch(API, { method: 'GET', credentials: 'same-origin' })
                    .then(function (res) {
                        if (handleAuthFailure(res)) return null;
                        return res.json();
                    })
                    .then(function (data) {
                        if (!data) return;

                        if (!data.ok) {
                            summary.textContent =
                                (data.error || 'Gagal memuat data stok.');
                            return;
                        }

                        render(data.items || []);
                    })
                    .catch(function () {
                        summary.textContent =
                            'Gagal memuat data stok. Silakan muat ulang.';
                    })
                    .then(function () {
                        refresh.disabled = false;
                    });
            }

            refresh.addEventListener('click', load);
            load();
        })();
    </script>
</body>
</html>
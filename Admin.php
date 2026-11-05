<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_page();

$admin = auth_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <title>Admin — Stok Produk</title>
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

    <main class="admin-page">
        <section class="admin-panel" aria-labelledby="admin-stock-heading">
            <div class="admin-panel-head">
                <div>
                    <h1 id="admin-stock-heading">Stok Produk</h1>
                    <p class="admin-panel-sub">
                        Atur jumlah produk jadi yang tersedia. Produk dengan stok 0
                        otomatis ditandai habis dan tidak bisa dipesan pelanggan.
                    </p>
                </div>
                <button type="button" id="admin-refresh" class="admin-btn">Muat ulang</button>
            </div>

            <p id="admin-summary" class="admin-summary" role="status" aria-live="polite">
                Memuat data stok...
            </p>

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
    </main>

    <script src="/assets/js/session.js"></script>
    <script src="/assets/js/nav.js"></script>
    <script>
        // Admins must complete their profile before using the console.
        enforceProfileGate();

        // Show any flash messages (e.g. redirected from Profile.php).
        showFlashMessage();
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

                const saveBtn = makeButton('Simpan', 'admin-btn admin-btn-primary', function () {
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

            function updateSummary() {
                const total = items.length;
                const habis = items.filter(function (i) { return i.stok <= 0; }).length;
                summary.textContent =
                    total + ' produk · ' + habis + ' habis · ' +
                    (total - habis) + ' tersedia';
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
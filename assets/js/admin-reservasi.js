/**
 * admin-reservasi.js — release a table that a customer is holding.
 *
 * Reads /api/admin/reservasi.php and renders the occupied tables into
 * #admin-reservasi-body. Releasing closes the reservation so the table can be
 * booked again; it does not delete the history.
 */

(function () {
    'use strict';

    var API  = '/api/admin/reservasi.php';
    var body = document.getElementById('admin-reservasi-body');
    var note = document.getElementById('admin-reservasi-note');

    if (!body) return;

    function handleAuth(res) {
        if (res.status === 401 || res.status === 403) {
            window.location.href = '/Login.php';
            return true;
        }
        return false;
    }

    function say(text, kind) {
        note.textContent = text;
        note.className = 'form-alert ' + (kind === 'error' ? 'form-alert--error' : 'form-alert--ok');
    }

    function emptyRow(text) {
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = 6;
        td.className = 'admin-loading';
        td.textContent = text;
        tr.appendChild(td);
        return tr;
    }

    function shortTime(value) {
        if (!value) return '-';
        var d = new Date(value.replace(' ', 'T'));
        if (isNaN(d.getTime())) return value;
        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
        }).format(d);
    }

    function buildRow(item) {
        var tr = document.createElement('tr');
        tr.dataset.reservasiId = String(item.reservasi_id);

        var meja = document.createElement('td');
        meja.className = 'admin-product-name';
        meja.textContent = item.meja;

        var nama = document.createElement('td');
        nama.textContent = item.nama || '-';
        if (item.username) {
            var user = document.createElement('div');
            user.className = 'page-head__meta';
            user.textContent = '@' + item.username;
            nama.appendChild(user);
        }

        var orang = document.createElement('td');
        orang.textContent = item.jumlah_orang + ' orang';

        var waktu = document.createElement('td');
        waktu.textContent = item.tanggal + ' · ' + item.waktu;

        var sejak = document.createElement('td');
        sejak.textContent = shortTime(item.dibuat_pada);

        var aksi = document.createElement('td');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'button button--sm';
        btn.textContent = 'Aktifkan kembali';
        btn.addEventListener('click', function () {
            if (!confirm('Aktifkan kembali meja ' + item.meja + '?')) return;
            release(item, btn);
        });
        aksi.appendChild(btn);

        tr.append(meja, nama, orang, waktu, sejak, aksi);
        return tr;
    }

    function render(items) {
        body.replaceChildren();

        if (!items.length) {
            body.appendChild(emptyRow('Tidak ada meja yang sedang terisi.'));
            return;
        }

        items.forEach(function (item) {
            body.appendChild(buildRow(item));
        });
    }

    function load() {
        fetch(API, { credentials: 'same-origin' })
            .then(function (res) {
                if (handleAuth(res)) return null;
                return res.json();
            })
            .then(function (data) {
                if (!data) return;

                if (!data.ok) {
                    body.replaceChildren(emptyRow((data && data.error) || 'Data meja tidak dapat dimuat.'));
                    return;
                }

                render(data.items || []);
            })
            .catch(function () {
                body.replaceChildren(emptyRow('Data meja tidak dapat dimuat.'));
            });
    }

    function release(item, btn) {
        btn.disabled = true;

        fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ action: 'release', reservasi_id: item.reservasi_id })
        })
            .then(function (res) {
                if (handleAuth(res)) return null;
                return res.json();
            })
            .then(function (data) {
                if (!data) return;

                if (!data.ok) {
                    say((data && data.error) || 'Gagal mengaktifkan meja.', 'error');
                    btn.disabled = false;
                    return;
                }

                say(data.note || 'Meja diaktifkan kembali.');
                load();
            })
            .catch(function () {
                say('Gagal mengaktifkan meja. Silakan coba lagi.', 'error');
                btn.disabled = false;
            });
    }

    var refresh = document.getElementById('admin-reservasi-refresh');
    if (refresh) {
        refresh.addEventListener('click', function () {
            note.textContent = '';
            note.className = 'form-alert';
            load();
        });
    }

    load();
})();
/**
 * owner-menu.js — catalog management for the owner console.
 *
 * Talks to /api/owner/menu.php: GET the catalog, POST create / update /
 * delete / restore.
 */

(function () {
    'use strict';

    var API = '/api/owner/menu.php';

    var body      = document.getElementById('owner-menu-body');
    var form      = document.getElementById('menu-form');
    var feedback  = document.getElementById('menu-feedback');

    if (!body) return;

    var idIn     = document.getElementById('menu-id');
    var namaIn   = document.getElementById('menu-nama');
    var katIn    = document.getElementById('menu-kategori');
    var hargaIn  = document.getElementById('menu-harga');
    var stokIn   = document.getElementById('menu-stok');
    var gambarIn = document.getElementById('menu-gambar');
    var gambarPathIn = document.getElementById('menu-gambar-path');
    var dropzone = document.getElementById('menu-dropzone');
    var fileIn   = document.getElementById('menu-gambar-file');
    var dzEmpty  = document.getElementById('menu-dropzone-empty');
    var dzFilled = document.getElementById('menu-dropzone-filled');
    var preview  = document.getElementById('menu-gambar-preview');
    var nameEl   = document.getElementById('menu-gambar-name');
    var sizeEl   = document.getElementById('menu-gambar-size');
    var clearBtn = document.getElementById('menu-gambar-clear');
    var descIn   = document.getElementById('menu-deskripsi');
    var submit   = document.getElementById('menu-submit');
    var newBtn   = document.getElementById('menu-new');
    var cancel   = document.getElementById('menu-cancel');

    var rupiah = new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', maximumFractionDigits: 0
    });

    var items = [];
    var kategori = [];

    /* --- Image dropzone ------------------------------------------------ */

    var MAX_SOURCE_BYTES = 8 * 1024 * 1024;  // reject absurd source files
    var MAX_DIMENSION    = 900;              // downscale target
    var JPEG_QUALITY     = 0.82;

    function bytes(n) {
        if (n >= 1048576) return (n / 1048576).toFixed(1) + ' MB';
        return Math.round(n / 1024) + ' KB';
    }

    function showPreview(dataUrl, label, sizeText) {
        gambarIn.value = dataUrl;
        preview.src = dataUrl;
        nameEl.textContent = label;
        sizeEl.textContent = sizeText;
        dzEmpty.hidden = true;
        dzFilled.hidden = false;
        dropzone.classList.remove('is-invalid');
    }

    function clearImage() {
        gambarIn.value = '';
        fileIn.value = '';
        preview.removeAttribute('src');
        dzFilled.hidden = true;
        dzEmpty.hidden = false;
    }

    function failImage(message) {
        dropzone.classList.add('is-invalid');
        say(message, 'error');
    }

    /** Downscale to MAX_DIMENSION and return a JPEG data URL. */
    function readImage(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();

            img.onload = function () {
                URL.revokeObjectURL(url);

                var scale = Math.min(1, MAX_DIMENSION / Math.max(img.width, img.height));
                var w = Math.round(img.width * scale);
                var h = Math.round(img.height * scale);

                var canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;

                var ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, w, h);
                ctx.drawImage(img, 0, 0, w, h);

                resolve(canvas.toDataURL('image/jpeg', JPEG_QUALITY));
            };

            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Gambar tidak dapat dibaca.'));
            };

            img.src = url;
        });
    }

    function acceptFile(file) {
        if (!file) return;

        if (!file.type || file.type.indexOf('image/') !== 0) {
            failImage('File harus berupa gambar (JPG, PNG, atau WEBP).');
            return;
        }

        if (file.size > MAX_SOURCE_BYTES) {
            failImage('Ukuran gambar maksimal 8 MB.');
            return;
        }

        readImage(file)
            .then(function (dataUrl) {
                var approx = Math.round((dataUrl.length * 3) / 4);
                showPreview(dataUrl, file.name, 'Dioptimalkan ke ' + bytes(approx));
                say('Gambar siap disimpan.', 'ok');
            })
            .catch(function (err) {
                failImage(err.message || 'Gambar tidak dapat dibaca.');
            });
    }

    if (dropzone) {
        dropzone.addEventListener('click', function (e) {
            if (e.target.closest('#menu-gambar-clear')) return;
            fileIn.click();
        });

        dropzone.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                fileIn.click();
            }
        });

        fileIn.addEventListener('change', function () {
            acceptFile(fileIn.files[0]);
        });

        ['dragenter', 'dragover'].forEach(function (type) {
            dropzone.addEventListener(type, function (e) {
                e.preventDefault();
                dropzone.classList.add('is-dragging');
            });
        });

        ['dragleave', 'drop'].forEach(function (type) {
            dropzone.addEventListener(type, function (e) {
                e.preventDefault();
                dropzone.classList.remove('is-dragging');
            });
        });

        dropzone.addEventListener('drop', function (e) {
            var dt = e.dataTransfer;
            if (!dt || !dt.files || !dt.files.length) return;
            acceptFile(dt.files[0]);
        });

        clearBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            clearImage();
            clearFeedback();
        });
    }

    function say(text, kind) {
        feedback.textContent = text;
        feedback.className = 'form-alert ' + (kind === 'error' ? 'form-alert--error' : 'form-alert--ok');
    }

    function clearFeedback() {
        feedback.textContent = '';
        feedback.className = 'form-alert';
    }

    function handleAuth(res) {
        if (res.status === 401 || res.status === 403) {
            window.location.href = '/Login.php';
            return true;
        }
        return false;
    }

    function fillCategories() {
        katIn.replaceChildren();

        kategori.forEach(function (k) {
            var opt = document.createElement('option');
            opt.value = String(k.kategori_id);
            opt.textContent = k.nama_kategori;
            katIn.appendChild(opt);
        });
    }

    function resetForm() {
        form.reset();
        idIn.value = '';
        stokIn.value = '10';
        submit.textContent = 'Simpan menu';
        if (kategori.length) katIn.value = String(kategori[0].kategori_id);
        clearImage();
        dropzone.classList.remove('is-invalid', 'is-dragging');
        gambarPathIn.value = '';
    }

    function openCreate() {
        resetForm();
        form.hidden = false;
        namaIn.focus();
    }

    function openEdit(item) {
        clearFeedback();
        form.hidden = false;
        idIn.value = String(item.menu_id);
        namaIn.value = item.nama_menu;
        katIn.value = item.kategori_id ? String(item.kategori_id) : '';
        hargaIn.value = String(Math.round(item.harga));
        stokIn.value = String(item.stok);
        if (item.gambar) {
            showPreview(item.gambar, 'Gambar saat ini', 'Tersimpan');
        } else {
            clearImage();
        }
        gambarPathIn.value = '';
        descIn.value = item.deskripsi || '';
        submit.textContent = 'Perbarui menu';
        namaIn.focus();
    }

    function closeForm() {
        form.hidden = true;
        resetForm();
    }

    function clearForm() {
        closeForm();
        clearFeedback();
    }

    function thumb(item) {
        var src = item.gambar || '/assets/documentasi/1f5515c814af4b017c4106159f9214a88b93d314.png';
        var img = document.createElement('img');
        img.className = 'gambar-preview';
        img.src = src;
        img.alt = item.nama_menu;
        img.loading = 'lazy';
        return img;
    }

    function buildRow(item) {
        var tr = document.createElement('tr');
        tr.dataset.menuId = String(item.menu_id);
        if (!item.aktif) tr.classList.add('is-inactive');

        var cellImg = document.createElement('td');
        cellImg.appendChild(thumb(item));

        var cellName = document.createElement('td');
        cellName.className = 'admin-product-name';
        cellName.textContent = item.nama_menu;
        if (item.deskripsi) {
            var desc = document.createElement('div');
            desc.className = 'page-head__meta';
            desc.textContent = item.deskripsi;
            cellName.appendChild(desc);
        }

        var cellKat = document.createElement('td');
        cellKat.textContent = item.nama_kategori || '-';

        var cellHarga = document.createElement('td');
        cellHarga.textContent = rupiah.format(item.harga);

        var cellStok = document.createElement('td');
        cellStok.textContent = String(item.stok);

        var cellStatus = document.createElement('td');
        var badge = document.createElement('span');
        badge.className = 'status-badge ' + (item.status === 'tersedia' ? 'tersedia' : 'habis');
        badge.textContent = item.status === 'tersedia' ? 'Tersedia' : 'Habis';
        cellStatus.appendChild(badge);

        var cellActions = document.createElement('td');
        var wrap = document.createElement('div');
        wrap.className = 'row-actions';

        var edit = document.createElement('button');
        edit.type = 'button';
        edit.className = 'button button--ghost button--sm';
        edit.textContent = 'Ubah';
        edit.addEventListener('click', function () { openEdit(item); });

        var del = document.createElement('button');
        del.type = 'button';
        del.className = 'button button--ghost button--sm';
        del.textContent = item.aktif ? 'Hapus' : 'Aktifkan';

        del.addEventListener('click', function () {
            if (item.aktif) {
                if (!confirm('Hapus "' + item.nama_menu + '" dari katalog?')) return;
                send({ action: 'delete', menu_id: item.menu_id });
            } else {
                send({ action: 'restore', menu_id: item.menu_id });
            }
        });

        wrap.append(edit, del);
        cellActions.appendChild(wrap);

        tr.append(cellImg, cellName, cellKat, cellHarga, cellStok, cellStatus, cellActions);
        return tr;
    }

    function render() {
        body.replaceChildren();

        if (!items.length) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 7;
            td.className = 'admin-loading';
            td.textContent = 'Belum ada menu. Tambahkan yang pertama.';
            tr.appendChild(td);
            body.appendChild(tr);
            return;
        }

        items.forEach(function (item) {
            body.appendChild(buildRow(item));
        });
    }

    function applyResponse(data, successText) {
        if (!data || !data.ok) {
            say((data && data.error) || 'Gagal menyimpan.', 'error');
            return false;
        }

        say(data.note || successText);
        return true;
    }

    function send(payload, successText) {
        return fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        })
            .then(function (res) {
                if (handleAuth(res)) return null;
                return res.json();
            })
            .then(function (data) {
                if (!data) return false;
                var ok = applyResponse(data, successText || 'Tersimpan.');

                if (ok) {
                    closeForm();
                    load();
                }
                return ok;
            })
            .catch(function () {
                say('Gagal menyimpan. Silakan coba lagi.', 'error');
                return false;
            });
    }

    function load() {
        fetch(API, { credentials: 'same-origin' })
            .then(function (res) {
                if (handleAuth(res)) return null;
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.ok) {
                    body.replaceChildren();
                    var tr = document.createElement('tr');
                    var td = document.createElement('td');
                    td.colSpan = 7;
                    td.className = 'admin-loading';
                    td.textContent = 'Katalog tidak dapat dimuat.';
                    tr.appendChild(td);
                    body.appendChild(tr);
                    return;
                }

                items = data.items || [];
                kategori = data.kategori || [];
                fillCategories();
                resetForm();
                render();
            })
            .catch(function () {
                say('Katalog tidak dapat dimuat.', 'error');
            });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var payload = {
            menu_id:     idIn.value ? Number(idIn.value) : undefined,
            nama_menu:   namaIn.value.trim(),
            kategori_id: Number(katIn.value),
            harga:       Number(hargaIn.value),
            stok:        Number(stokIn.value),
            gambar:      gambarIn.value || gambarPathIn.value.trim(),
            deskripsi:   descIn.value.trim()
        };

        if (idIn.value) {
            payload.action = 'update';
            send(payload, 'Menu diperbarui.');
        } else {
            delete payload.menu_id;
            payload.action = 'create';
            send(payload, 'Menu ditambahkan.');
        }
    });

    if (newBtn) newBtn.addEventListener('click', openCreate);
    if (cancel) cancel.addEventListener('click', clearForm);

    load();
})();
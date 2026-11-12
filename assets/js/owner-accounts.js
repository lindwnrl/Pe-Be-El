/**
 * owner-accounts.js — create and remove admin/owner accounts.
 *
 * Talks to /api/owner/accounts.php. Only staff accounts are listed; customers
 * register themselves.
 */

(function () {
    'use strict';

    var API  = '/api/owner/accounts.php';
    var body = document.getElementById('owner-accounts-body');
    var form = document.getElementById('account-form');
    var note = document.getElementById('account-feedback');

    if (!body) return;

    var usernameIn = document.getElementById('account-username');
    var passwordIn = document.getElementById('account-password');
    var roleIn     = document.getElementById('account-role');
    var submit     = document.getElementById('account-submit');
    var toggle     = document.getElementById('account-new');
    var cancel     = document.getElementById('account-cancel');

    var roleLabel = { admin: 'Admin', owner: 'Owner' };

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

    function clearNote() {
        note.textContent = '';
        note.className = 'form-alert';
    }

    function emptyRow(text) {
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = 4;
        td.className = 'admin-loading';
        td.textContent = text;
        tr.appendChild(td);
        return tr;
    }

    function buildRow(item) {
        var tr = document.createElement('tr');
        tr.dataset.akunId = String(item.akun_id);

        var user = document.createElement('td');
        user.className = 'admin-product-name';
        user.textContent = item.username;

        var name = document.createElement('td');
        name.textContent = item.nama || '—';

        var role = document.createElement('td');
        var badge = document.createElement('span');
        badge.className = 'status-badge ' + (item.role === 'owner' ? 'info' : 'tersedia');
        badge.textContent = roleLabel[item.role] || item.role;
        role.appendChild(badge);

        var aksi = document.createElement('td');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'button button--ghost button--sm';
        btn.textContent = 'Hapus';
        btn.addEventListener('click', function () {
            if (!confirm('Hapus akun "' + item.username + '"?')) return;
            remove(item, btn);
        });
        aksi.appendChild(btn);

        tr.append(user, name, role, aksi);
        return tr;
    }

    function render(items) {
        body.replaceChildren();

        if (!items.length) {
            body.appendChild(emptyRow('Belum ada akun staf.'));
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
                    body.replaceChildren(emptyRow((data && data.error) || 'Akun tidak dapat dimuat.'));
                    return;
                }

                render(data.items || []);
            })
            .catch(function () {
                body.replaceChildren(emptyRow('Akun tidak dapat dimuat.'));
            });
    }

    function remove(item, btn) {
        btn.disabled = true;

        fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ action: 'delete', akun_id: item.akun_id })
        })
            .then(function (res) {
                if (handleAuth(res)) return null;
                return res.json();
            })
            .then(function (data) {
                if (!data) return;

                if (!data.ok) {
                    say((data && data.error) || 'Gagal menghapus akun.', 'error');
                    btn.disabled = false;
                    return;
                }

                say(data.note || 'Akun dihapus.');
                load();
            })
            .catch(function () {
                say('Gagal menghapus akun.', 'error');
                btn.disabled = false;
            });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var payload = {
                action:   'create',
                username: usernameIn.value.trim(),
                password: passwordIn.value,
                role:     roleIn.value
            };

            submit.disabled = true;

            fetch(API, {
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
                    if (!data) return;

                    if (!data.ok) {
                        say((data && data.error) || 'Gagal membuat akun.', 'error');
                        return;
                    }

                    form.reset();
                    form.hidden = true;
                    say(data.note || 'Akun dibuat.');
                    load();
                })
                .catch(function () {
                    say('Gagal membuat akun. Silakan coba lagi.', 'error');
                })
                .then(function () {
                    submit.disabled = false;
                });
        });
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            clearNote();
            form.hidden = !form.hidden;
            if (!form.hidden) usernameIn.focus();
        });
    }

    if (cancel) {
        cancel.addEventListener('click', function () {
            form.reset();
            form.hidden = true;
            clearNote();
        });
    }

    load();
})();
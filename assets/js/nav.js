/**
 * nav.js — single source of truth for the site navigation.
 *
 * Renders into <ul id="site-nav">:
 *   Home · Menu · Kursi · [Profile] · [Admin] · [Login | Logout]
 *
 * Profile is shown only when logged in; Admin only for admins.
 * Requires session.js (loaded first).
 */

(function () {
    'use strict';

    function createItem(label, href, id) {
        const li = document.createElement('li');
        const a  = document.createElement('a');
        a.href = href;
        a.textContent = label;
        if (id) a.id = id;
        li.appendChild(a);
        return li;
    }

    function createLogoutItem() {
        const li = document.createElement('li');
        const a  = document.createElement('a');
        a.href = '#';
        a.id = 'nav-logout';
        a.textContent = 'Logout';
        a.style.color = '#b45309';
        a.title = 'Keluar dari sesi saat ini';
        a.addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm('Yakin ingin keluar?')) return;

            fetch('/api/logout.php', { method: 'POST', credentials: 'same-origin' })
                .catch(function () { /* log out locally regardless */ })
                .then(function () {
                    clearSession();
                    window.location.href = '/Dashboard.php';
                });
        });
        li.appendChild(a);
        return li;
    }

    function buildNav() {
        const list = document.getElementById('site-nav');
        if (!list) return;

        const loggedIn = isLoggedIn();
        const admin    = isAdmin();

        const items = [
            createItem('Home', '/Dashboard.php'),
            createItem('Menu', '/Menu.php', 'nav-menu'),
            createItem('Kursi', '/Kursi.php')
        ];

        if (loggedIn) {
            items.push(createItem('Profile', '/Profile.php', 'nav-profile'));
        }

        if (admin) {
            items.push(createItem('Admin', '/Admin.php', 'nav-admin'));
        }

        items.push(loggedIn
            ? createLogoutItem()
            : createItem('Login', '/Login.php', 'nav-login'));

        list.replaceChildren.apply(list, items);
    }

    buildNav();
})();
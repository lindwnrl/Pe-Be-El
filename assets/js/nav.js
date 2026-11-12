/**
 * nav.js — single source of truth for the site navigation.
 *
 * Renders into <ul id="site-nav">.
 *
 *  Landing page, guest (body[data-page="landing"]):
 *      Home · Profile · Menu · Location · Login        (same-page anchors)
 *
 *  Everywhere else:
 *      Home · Menu · Kursi · [Profile] · [Admin] · [Login | Logout]
 *
 * Also handles the mobile hamburger, the active-link state, and the
 * section scroll-spy on the landing page. Requires session.js (loaded first).
 */

(function () {
    'use strict';

    var isLanding = document.body.dataset.page === 'landing';
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ── Item builders ─────────────────────────────────────────────────── */

    function createItem(label, href, id) {
        var li = document.createElement('li');
        var a  = document.createElement('a');
        a.href = href;
        a.textContent = label;
        if (id) a.id = id;
        li.appendChild(a);
        return li;
    }

    function createLogoutItem() {
        var li = document.createElement('li');
        var a  = document.createElement('a');
        a.href = '#';
        a.id = 'nav-logout';
        a.textContent = 'Logout';
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

    /* ── Same-page anchors ─────────────────────────────────────────────── */

    function wireAnchorScroll(root) {
        root.addEventListener('click', function (e) {
            var link = e.target.closest('a[href^="#"]');
            if (!link) return;

            var hash = link.getAttribute('href');
            if (!hash || hash === '#') return;

            var target = document.querySelector(hash);
            if (!target) return; // let the browser handle it

            e.preventDefault();
            target.scrollIntoView({
                behavior: reduceMotion ? 'auto' : 'smooth',
                block: 'start'
            });

            // Keep the URL shareable without a second jump.
            if (history.replaceState) {
                history.replaceState(null, '', hash);
            }
        });
    }

    /* ── Active link (path based) ──────────────────────────────────────── */

    function markActiveByPath(list) {
        var path = window.location.pathname.replace(/\/+$/, '') || '/';

        Array.prototype.forEach.call(list.querySelectorAll('a'), function (a) {
            var href = a.getAttribute('href');
            if (!href || href === '#' || href.charAt(0) === '#') return;

            var target = href.replace(/\/+$/, '') || '/';
            if (target === path) {
                a.classList.add('is-active');
                a.setAttribute('aria-current', 'page');
            }
        });
    }

    /* ── Scroll-spy (landing only) ─────────────────────────────────────── */

    function wireScrollSpy(list) {
        var links = {};
        Array.prototype.forEach.call(
            list.querySelectorAll('a[href^="#"]'),
            function (a) { links[a.getAttribute('href').slice(1)] = a; }
        );

        var sections = Object.keys(links)
            .map(function (id) { return document.getElementById(id); })
            .filter(Boolean);

        if (!sections.length || !('IntersectionObserver' in window)) return;

        function setActive(id) {
            Object.keys(links).forEach(function (key) {
                var a = links[key];
                if (key === id) {
                    a.classList.add('is-active');
                    a.setAttribute('aria-current', 'location');
                } else {
                    a.classList.remove('is-active');
                    a.removeAttribute('aria-current');
                }
            });
        }

        var visible = new Map();

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                visible.set(entry.target.id, entry.isIntersecting ? entry.intersectionRatio : 0);
            });

            var bestId = null;
            var bestRatio = 0;

            visible.forEach(function (ratio, id) {
                if (ratio > bestRatio) {
                    bestRatio = ratio;
                    bestId = id;
                }
            });

            if (bestId) setActive(bestId);
        }, {
            rootMargin: '-45% 0px -45% 0px',
            threshold: [0, 0.25, 0.5, 0.75, 1]
        });

        sections.forEach(function (section) { observer.observe(section); });

        // Keep the first item active while above the first section.
        setActive(sections[0].id);
    }

    /* ── Sidebar (admin / owner shell) ─────────────────────────────────── */

    function buildSidebar() {
        var nav = document.getElementById('sidebar-nav');
        if (!nav) return null;

        var path = window.location.pathname.replace(/\/+$/, '');
        var hash = window.location.hash;

        var items = [];

        if (isAdmin()) {
            items.push({ label: 'Stok Produk', href: '/Admin.php', id: 'nav-admin-stock', icon: '▤' });
            // items.push({ label: 'Laporan', href: '/Admin.php#laporan', id: 'nav-admin-report', icon: '▥' });
        }

        if (isOwner()) {
            items.push({ label: 'Menu & Harga', href: '/Owner.php', id: 'nav-owner-menu', icon: '◈' });
            // items.push({ label: 'Laporan', href: '/Owner.php#laporan', id: 'nav-owner-report', icon: '▥' });
        }

        items.push(
            { label: 'Menu', href: '/Menu.php', id: 'nav-menu', icon: '◇' },
            { label: 'Kursi', href: '/Kursi.php', id: 'nav-kursi', icon: '▣' },
            { label: 'Profile', href: '/Profile.php', id: 'nav-profile', icon: '◉' }
        );

        var links = items.map(function (item) {
            var a = document.createElement('a');
            a.href = item.href;
            a.id = item.id;

            var icon = document.createElement('span');
            icon.className = 'sidebar__icon';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = item.icon;

            var text = document.createElement('span');
            text.textContent = item.label;

            a.append(icon, text);

            var parts = item.href.split('#');
            var itemPath = parts[0];
            var itemHash = parts[1] ? '#' + parts[1] : '';

            var matches = path === itemPath && (itemHash === '' || hash === itemHash);

            // A bare page link stays active when no hash item claims the URL.
            if (matches && !(itemHash === '' && hash !== '')) {
                a.classList.add('is-active');
                a.setAttribute('aria-current', 'page');
            }
            return a;
        });

        var logout = document.createElement('a');
        logout.href = '#';
        logout.id = 'nav-logout';
        logout.innerHTML = '<span class="sidebar__icon" aria-hidden="true">⏻</span><span>Logout</span>';
        logout.addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm('Yakin ingin keluar?')) return;
            fetch('/api/logout.php', { method: 'POST', credentials: 'same-origin' })
                .catch(function () {})
                .then(function () {
                    clearSession();
                    window.location.href = '/Dashboard.php';
                });
        });
        links.push(logout);

        nav.replaceChildren.apply(nav, links);
        return nav;
    }

    /* ── Render ────────────────────────────────────────────────────────── */

    function buildNav() {
        var list = document.getElementById('site-nav');
        if (!list) return;

        var loggedIn = isLoggedIn();
        var admin    = isAdmin();

        var items;

        if (isLanding && !loggedIn) {
            items = [
                createItem('Home', '#home', 'nav-home'),
                createItem('Profile', '#profile', 'nav-profile'),
                createItem('Menu', '#menu', 'nav-menu'),
                createItem('Location', '#location', 'nav-location'),
                createItem('Login', '/Login.php', 'nav-login')
            ];
        } else {
            items = [
                createItem('Menu', '/Menu.php', 'nav-menu'),
                createItem('Kursi', '/Kursi.php')
            ];

            // Home is the public landing page, so it belongs to guests only.
            if (!loggedIn) {
                items.unshift(createItem('Home', '/Dashboard.php'));
            }

            if (loggedIn) {
                items.push(createItem('Profile', '/Profile.php', 'nav-profile'));
            }

            if (admin) {
                items.push(createItem('Admin', '/Admin.php', 'nav-admin'));
            }

            items.push(loggedIn
                ? createLogoutItem()
                : createItem('Login', '/Login.php', 'nav-login'));
        }

        list.replaceChildren.apply(list, items);

        if (isLanding && !loggedIn) {
            // Bind once on the document so hero CTAs and footer links scroll too.
            wireAnchorScroll(document);
            wireScrollSpy(list);
        } else {
            markActiveByPath(list);
        }
    }

    /* ── Mobile hamburger ──────────────────────────────────────────────── */

    function wireToggle() {
        var header = document.querySelector('.site-header');
        var toggle = document.querySelector('.nav-toggle');
        if (!header || !toggle) return;

        function setOpen(open) {
            header.classList.toggle('nav-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', function () {
            setOpen(!header.classList.contains('nav-open'));
        });

        // Keyboard: Escape closes the panel.
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });

        // Selecting a destination closes the panel.
        header.addEventListener('click', function (e) {
            if (e.target.closest('a') && window.innerWidth <= 720) setOpen(false);
        });
    }

    /* ── Header elevation on scroll ────────────────────────────────────── */

    function wireScrollShadow() {
        var header = document.querySelector('.site-header');
        if (!header) return;

        function update() {
            header.classList.toggle('is-scrolled', window.scrollY > 4);
        }

        window.addEventListener('scroll', update, { passive: true });
        update();
    }

    buildSidebar();
    buildNav();
    wireToggle();
    wireScrollShadow();
})();

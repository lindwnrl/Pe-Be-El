/**
 * best-sellers.js — top products by units sold, beside the sales chart.
 *
 * Reads /api/best-sellers.php and renders ranked rows into #best-sellers.
 */

(function () {
    'use strict';

    var list = document.getElementById('best-sellers');
    if (!list) return;

    var fromIn = document.getElementById('sales-from');
    var toIn   = document.getElementById('sales-to');
    var apply  = document.getElementById('sales-apply');

    var rupiah = new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', maximumFractionDigits: 0
    });

    var medal = ['1', '2', '3'];

    function handleAuth(res) {
        if (res.status === 401 || res.status === 403) {
            window.location.href = '/Login.php';
            return true;
        }
        return false;
    }

    function emptyState(text) {
        var p = document.createElement('p');
        p.className = 'chart-empty';
        p.textContent = text;
        list.replaceChildren(p);
    }

    function row(item, index, max) {
        var wrap = document.createElement('div');
        wrap.className = 'seller' + (index === 0 ? ' is-top' : '');

        var rank = document.createElement('span');
        rank.className = 'seller__rank';
        rank.textContent = medal[index] || String(index + 1);

        var media = document.createElement('span');
        media.className = 'seller__thumb';

        if (item.gambar) {
            var img = document.createElement('img');
            img.src = item.gambar;
            img.alt = item.nama_menu;
            img.loading = 'lazy';
            media.appendChild(img);
        }

        var body = document.createElement('div');
        body.className = 'seller__body';

        var name = document.createElement('strong');
        name.className = 'seller__name';
        name.textContent = item.nama_menu;

        var bar = document.createElement('span');
        bar.className = 'seller__bar';

        var fill = document.createElement('span');
        fill.className = 'seller__fill';
        fill.style.width = (max > 0 ? Math.max(4, (item.terjual / max) * 100) : 0) + '%';
        bar.appendChild(fill);

        body.append(name, bar);

        var count = document.createElement('span');
        count.className = 'seller__count';
        count.innerHTML = '<strong>' + item.terjual + '</strong> terjual';

        wrap.title = item.nama_menu + ' — ' + item.terjual + ' terjual · ' +
                     rupiah.format(item.pendapatan);

        wrap.append(rank, media, body, count);
        return wrap;
    }

    function load() {
        var params = [];

        if (fromIn && fromIn.value) params.push('from=' + encodeURIComponent(fromIn.value));
        if (toIn && toIn.value)     params.push('to=' + encodeURIComponent(toIn.value));
        params.push('limit=3');

        fetch('/api/best-sellers.php?' + params.join('&'), { credentials: 'same-origin' })
            .then(function (res) {
                if (handleAuth(res)) return null;
                return res.json();
            })
            .then(function (data) {
                if (!data) return;

                if (!data.ok) {
                    emptyState((data && data.error) || 'Data terlaris tidak dapat dimuat.');
                    return;
                }

                if (!data.items || !data.items.length) {
                    emptyState('Belum ada penjualan pada rentang ini.');
                    return;
                }

                var max = data.items.reduce(function (m, i) { return Math.max(m, i.terjual); }, 0);
                list.replaceChildren();

                data.items.forEach(function (item, index) {
                    list.appendChild(row(item, index, max));
                });
            })
            .catch(function () {
                emptyState('Data terlaris tidak dapat dimuat.');
            });
    }

    // Reuse the same date controls as the sales chart, so one range drives both.
    if (apply) apply.addEventListener('click', load);
    if (fromIn) fromIn.addEventListener('change', load);
    if (toIn) toIn.addEventListener('change', load);

    load();
})();
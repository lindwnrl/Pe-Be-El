/**
 * sales-chart.js — daily revenue chart for the admin and owner consoles.
 *
 * Reads /api/sales-daily.php and renders bars into #sales-chart. Date inputs
 * and the "Terapkan" button live in includes/sales-panel.php.
 */

(function () {
    'use strict';

    var chart  = document.getElementById('sales-chart');
    var apply  = document.getElementById('sales-apply');
    var fromIn = document.getElementById('sales-from');
    var toIn   = document.getElementById('sales-to');
    var totalEl  = document.getElementById('sales-total');
    var ordersEl = document.getElementById('sales-orders');
    var avgEl    = document.getElementById('sales-avg');

    if (!chart) return;

    var rupiah = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    });

    var shortRupiah = function (value) {
        if (value >= 1000000) return (value / 1000000).toFixed(1).replace('.0', '') + 'jt';
        if (value >= 1000)    return Math.round(value / 1000) + 'k';
        return String(Math.round(value));
    };

    var dayLabel = function (iso) {
        var parts = iso.split('-');
        var d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
        return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short' }).format(d);
    };

    function isoToday(offsetDays) {
        var d = new Date();
        d.setDate(d.getDate() - offsetDays);
        return d.toISOString().slice(0, 10);
    }

    function setDefaults() {
        if (!fromIn.value) fromIn.value = isoToday(6);
        if (!toIn.value)   toIn.value   = isoToday(0);
    }

    function render(days) {
        chart.replaceChildren();

        var max = days.reduce(function (m, d) { return Math.max(m, d.total); }, 0);
        var peak = days.reduce(function (p, d) { return d.total > (p ? p.total : -1) ? d : p; }, null);

        if (max === 0) {
            var empty = document.createElement('p');
            empty.className = 'chart-empty';
            empty.textContent = 'Belum ada penjualan pada rentang ini.';
            chart.appendChild(empty);
            return;
        }

        days.forEach(function (day) {
            var col = document.createElement('div');
            col.className = 'chart__col';

            if (day.total === 0) col.classList.add('is-empty');
            if (peak && day.total === peak.total) col.classList.add('is-peak');

            var value = document.createElement('span');
            value.className = 'chart__value';
            value.textContent = day.total > 0 ? shortRupiah(day.total) : '';

            var bar = document.createElement('span');
            bar.className = 'chart__bar';
            bar.style.height = (day.total > 0 ? Math.max(3, (day.total / max) * 100) : 2) + '%';

            var label = document.createElement('span');
            label.className = 'chart__label';
            label.textContent = dayLabel(day.tanggal);

            col.title = dayLabel(day.tanggal) + ': ' + rupiah.format(day.total) +
                        ' · ' + day.pesanan + ' pesanan';

            col.append(value, bar, label);
            chart.appendChild(col);
        });
    }

    function load() {
        setDefaults();

        var url = '/api/sales-daily.php?from=' + encodeURIComponent(fromIn.value) +
                  '&to=' + encodeURIComponent(toIn.value);

        chart.setAttribute('aria-busy', 'true');

        fetch(url, { credentials: 'same-origin' })
            .then(function (res) {
                if (res.status === 401 || res.status === 403) {
                    window.location.href = '/Login.php';
                    return null;
                }
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.ok) {
                    chart.replaceChildren();
                    var err = document.createElement('p');
                    err.className = 'chart-empty';
                    err.textContent = (data && data.error) || 'Laporan tidak dapat dimuat.';
                    chart.appendChild(err);
                    return;
                }

                totalEl.textContent  = rupiah.format(data.total.pendapatan);
                ordersEl.textContent = String(data.total.pesanan);
                avgEl.textContent    = rupiah.format(data.total.rata_rata);

                render(data.days);
            })
            .catch(function () {
                chart.replaceChildren();
                var err = document.createElement('p');
                err.className = 'chart-empty';
                err.textContent = 'Laporan tidak dapat dimuat. Silakan coba lagi.';
                chart.appendChild(err);
            })
            .then(function () {
                chart.removeAttribute('aria-busy');
            });
    }

    if (apply) apply.addEventListener('click', load);
    if (fromIn) fromIn.addEventListener('change', load);
    if (toIn) toIn.addEventListener('change', load);

    load();
})();
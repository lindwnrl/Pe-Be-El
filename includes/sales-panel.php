<!--
  Shared daily-sales panel. Included by Admin.php and Owner.php.

  Expects nav.js to have already loaded; the chart itself is drawn by
  assets/js/sales-chart.js from /api/sales-daily.php.
-->
<section class="panel" id="laporan" aria-labelledby="laporan-title">
    <div class="panel__head">
        <div>
            <h2 id="laporan-title">Penjualan harian</h2>
            <p>Pendapatan per hari dari pesanan yang sudah selesai.</p>
        </div>

        <div class="panel__actions">
            <div class="field">
                <label for="sales-from">Dari</label>
                <input type="date" id="sales-from">
            </div>
            <div class="field">
                <label for="sales-to">Sampai</label>
                <input type="date" id="sales-to">
            </div>
            <button type="button" id="sales-apply" class="button button--sm">Terapkan</button>
        </div>
    </div>

    <div class="stat-grid">
        <article class="stat-card">
            <p class="stat-card__label">Pendapatan</p>
            <strong class="stat-card__value" id="sales-total">&mdash;</strong>
            <span class="stat-card__hint ok">Dalam rentang</span>
        </article>
        <article class="stat-card">
            <p class="stat-card__label">Pesanan</p>
            <strong class="stat-card__value" id="sales-orders">&mdash;</strong>
            <span class="stat-card__hint info">Selesai</span>
        </article>
        <article class="stat-card">
            <p class="stat-card__label">Rata-rata</p>
            <strong class="stat-card__value" id="sales-avg">&mdash;</strong>
            <span class="stat-card__hint warn">Per pesanan</span>
        </article>
    </div>

    <div class="chart" id="sales-chart" role="img" aria-label="Grafik penjualan harian">
        <!-- bars injected by sales-chart.js -->
    </div>
</section>
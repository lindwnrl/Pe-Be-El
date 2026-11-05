<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — Nina Rasa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bevan:ital@0;1&display=swap" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="/Dashboard.php" class="logo">
                <img src="/assets/documentasi/1f5515c814af4b017c4106159f9214a88b93d314.png" alt="Nina Rasa — Home">
            </a>
            <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-nav" aria-label="Buka navigasi">☰</button>
            <nav class="site-nav" aria-label="Navigasi utama">
                <ul id="site-nav"><!-- filled by nav.js --></ul>
            </nav>
        </div>
    </header>

    <main class="container menu-info">
        <div class="welcoming-info">
            <p>Dapur Nina Rasa</p>
            <h1>Silakan pilih menu kesukaanmu</h1>
            <p>Dibuat dengan sepenuh hati</p>
            <span class="status-buka" aria-hidden="true">Buka hari ini · 10.00–20.00</span>
        </div>

        <section class="menu-tools" aria-label="Cari dan filter menu">
            <label class="menu-search">
                <svg class="menu-search__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <circle cx="7" cy="7" r="4.5"></circle>
                    <path d="M10.5 10.5 L14 14"></path>
                </svg>
                <input id="menu-search" type="search" placeholder="Cari nama menu..." aria-label="Cari nama menu">
            </label>

            <label class="menu-category">
                <span>Kategori:</span>
                <select id="menu-category">
                    <option value="Semua">Semua</option>
                    <option value="Bakso">Bakso</option>
                    <option value="Mie Ayam">Mie Ayam</option>
                    <option value="Minuman">Minuman</option>
                    <option value="Tambahan">Tambahan</option>
                </select>
            </label>
        </section>

        <p id="menu-empty" hidden>Menu tidak ditemukan.</p>

        <div class="menu-layout">
            <div class="data-menu">
                <div class="menu" data-menu-id="1" data-category="Bakso" data-price="20000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image3.png" alt="Bakso Solo Campur">
                        </div>
                        <p>Bakso Solo Campur</p>
                        <div class="buy-row">
                            <p>Rp 20.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Bakso Solo Campur" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Bakso Solo Campur">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="2" data-category="Bakso" data-price="20000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image4.png" alt="Bakso Solo Tanpa Tahu">
                        </div>
                        <p>Bakso Solo Tanpa Tahu</p>
                        <div class="buy-row">
                            <p>Rp 20.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Bakso Solo Tanpa Tahu" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Bakso Solo Tanpa Tahu">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="3" data-category="Tambahan" data-price="15000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image5.png" alt="Cilok 1 Porsi">
                        </div>
                        <p>Cilok 1 Porsi</p>
                        <div class="buy-row">
                            <p>Rp 15.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Cilok 1 Porsi" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Cilok 1 Porsi">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="4" data-category="Mie Ayam" data-price="15000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image1.png" alt="Mie Ayam Mozarella">
                        </div>
                        <p>Mie Ayam Mozarella</p>
                        <div class="buy-row">
                            <p>Rp 15.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Mie Ayam Mozarella" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Mie Ayam Mozarella">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="5" data-category="Mie Ayam" data-price="15000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image6.png" alt="Mie Ayam Ceker">
                        </div>
                        <p>Mie Ayam Ceker</p>
                        <div class="buy-row">
                            <p>Rp 15.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Mie Ayam Ceker" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Mie Ayam Ceker">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="6" data-category="Mie Ayam" data-price="10000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image7.png" alt="Mie Ayam Original">
                        </div>
                        <p>Mie Ayam Original</p>
                        <div class="buy-row">
                            <p>Rp 10.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Mie Ayam Original" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Mie Ayam Original">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="7" data-category="Minuman" data-price="4000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image8.png" alt="Air Mineral">
                        </div>
                        <p>Air Mineral</p>
                        <div class="buy-row">
                            <p>Rp 4.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Air Mineral" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Air Mineral">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="8" data-category="Minuman" data-price="5000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image9.png" alt="Es Jeruk">
                        </div>
                        <p>Es Jeruk</p>
                        <div class="buy-row">
                            <p>Rp 5.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Es Jeruk" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Es Jeruk">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="9" data-category="Minuman" data-price="4000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image10.png" alt="Es Teh">
                        </div>
                        <p>Es Teh</p>
                        <div class="buy-row">
                            <p>Rp 4.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Es Teh" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Es Teh">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="10" data-category="Tambahan" data-price="5000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image11.png" alt="Ceker Ayam">
                        </div>
                        <p>Ceker Ayam</p>
                        <div class="buy-row">
                            <p>Rp 5.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Ceker Ayam" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Ceker Ayam">+</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="menu" data-menu-id="11" data-category="Tambahan" data-price="5000">
                    <article>
                        <div class="ready-status">
                            <span>Ready</span>
                            <img src="/assets/documentasi/image12.png" alt="Pangsit Goreng">
                        </div>
                        <p>Pangsit Goreng</p>
                        <div class="buy-row">
                            <p>Rp 5.000</p>
                            <div class="jumlah-barang">
                                <button type="button" class="quantity-btn minus" aria-label="Kurangi jumlah Pangsit Goreng" disabled>−</button>
                                <span class="quantity-value" aria-live="polite">0</span>
                                <button type="button" class="quantity-btn plus" aria-label="Tambah jumlah Pangsit Goreng">+</button>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <section class="cart-summary" aria-labelledby="cart-heading">
                <div class="cart-header">
                    <h2 id="cart-heading">Pesanan Saya</h2>
                    <span id="cart-total-quantity">0 item</span>
                </div>

                <p id="cart-empty">
                    Belum ada menu yang dipilih.
                </p>

                <ul id="cart-items"></ul>

                <div class="cart-footer">
                    <span>Total Pembayaran</span>
                    <strong id="cart-total-price">Rp0</strong>
                </div>

                <button id="checkout-button" type="button" disabled>
                    Lanjutkan Pesanan
                </button>
            </section>
        </div>

        <section id="order-confirmation" class="order-confirmation" hidden>
            <h2>Konfirmasi Pesanan</h2>
            <p class="confirmation-description">
                Periksa pesananmu sebelum melanjutkan.
            </p>

            <div class="order-review">
                <h3>Ringkasan Pesanan</h3>
                <ul id="order-review-items"></ul>

                <div class="order-review-total">
                    <span>Total Pembayaran</span>
                    <strong id="order-review-total">Rp0</strong>
                </div>
            </div>

            <form id="order-form">
                <div class="order-field">
                    <label for="customer-name">Nama Pemesan</label>
                    <input
                        type="text"
                        id="customer-name"
                        name="customerName"
                        placeholder="Masukkan nama kamu"
                        required
                        maxlength="100"
                    >
                </div>

                <div class="order-field">
                    <label for="order-type">Jenis Pesanan</label>
                    <select id="order-type" name="orderType" required>
                        <option value="Dine-in">Makan di tempat</option>
                        <option value="Takeaway">Bawa pulang</option>
                    </select>
                </div>

                <div class="order-field">
                    <label for="order-notes">Catatan (opsional)</label>
                    <textarea
                        id="order-notes"
                        name="orderNotes"
                        placeholder="Contoh: tanpa mie, kuah dipisah"
                        rows="3"
                        maxlength="500"
                    ></textarea>
                </div>

                <div class="order-actions">
                    <button type="button" id="back-to-menu">
                        Kembali ke Menu
                    </button>

                    <button type="submit" id="confirm-order">
                        Konfirmasi Pesanan
                    </button>
                </div>
            </form>

            <p id="order-result" role="status" aria-live="polite" hidden></p>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <p class="site-footer__brand">Nina Rasa</p>
            <p>Bakso &amp; Mie Ayam · Buka 10.00–20.00</p>
            <nav aria-label="Navigasi footer">
                <a href="/Dashboard.php">Home</a>
                <a href="/Menu.php">Menu</a>
                <a href="/Kursi.php">Kursi</a>
            </nav>
        </div>
    </footer>

    <script src="/assets/js/session.js"></script>
    <script src="/assets/js/nav.js"></script>
    <script src="/assets/js/menu.js"></script>
</body>
</html>

-- 1. Tabel pelanggan
CREATE TABLE pelanggan (
    pelanggan_id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    alamat VARCHAR(255) NOT NULL
);

-- 2. Tabel kategori_menu
CREATE TABLE kategori_menu (
    kategori_id SERIAL PRIMARY KEY,
    nama_kategori VARCHAR(30) NOT NULL
);

-- 3. Tabel menu
CREATE TABLE menu (
    menu_id SERIAL PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    harga NUMERIC(10,2) NOT NULL,
    stok INTEGER NOT NULL,
    status VARCHAR(30) NOT NULL,
    kategori_id INTEGER,
    FOREIGN KEY (kategori_id)
        REFERENCES kategori_menu(kategori_id)
);

-- 4. Tabel pesanan
CREATE TABLE pesanan (
    pesanan_id SERIAL PRIMARY KEY,
    tanggal_pesanan TIMESTAMP NOT NULL,
    total_harga NUMERIC(10,2) NOT NULL,
    status_pesanan VARCHAR(30) NOT NULL,
    pelanggan_id INTEGER,
    FOREIGN KEY (pelanggan_id)
        REFERENCES pelanggan(pelanggan_id)
);

-- 5. Tabel detail_pesanan
CREATE TABLE detail_pesanan (
    detail_id SERIAL PRIMARY KEY,
    jumlah INTEGER NOT NULL,
    harga NUMERIC(10,2) NOT NULL,
    subtotal NUMERIC(10,2) NOT NULL,
    pesanan_id INTEGER,
    menu_id INTEGER,
    FOREIGN KEY (pesanan_id)
        REFERENCES pesanan(pesanan_id),
    FOREIGN KEY (menu_id)
        REFERENCES menu(menu_id)
);

-- 6. Tabel pembayaran
CREATE TABLE pembayaran (
    pembayaran_id SERIAL PRIMARY KEY,
    metode_pembayaran VARCHAR(30) NOT NULL,
    jumlah_bayar NUMERIC(15,2) NOT NULL,
    tanggal_bayar TIMESTAMP NOT NULL,
    status_pembayaran INTEGER NOT NULL,
    pesanan_id INTEGER,
    FOREIGN KEY (pesanan_id)
        REFERENCES pesanan(pesanan_id)
);

-- 7. Tabel bahan
CREATE TABLE bahan (
    bahan_id SERIAL PRIMARY KEY,
    nama_bahan VARCHAR(100) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    stok INTEGER NOT NULL,
    menu_id INTEGER,
    FOREIGN KEY (menu_id)
        REFERENCES menu(menu_id)
);

-- 8. Tabel detail_bahan
CREATE TABLE detail_bahan (
    detail_bahan_id SERIAL PRIMARY KEY,
    jumlah NUMERIC(10,2) NOT NULL,
    bahan_id INTEGER,
    FOREIGN KEY (bahan_id)
        REFERENCES bahan(bahan_id)
);
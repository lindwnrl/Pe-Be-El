-- =============================================================================
-- Nina Rasa — full production setup
--
-- One script to provision a fresh PostgreSQL database (Neon, Supabase,
-- Vercel Postgres, or any managed provider). Run it once, top to bottom.
--
-- Local development is unaffected: this is the same schema the migrations
-- already produced, just consolidated for a clean environment.
--
--   psql "$DATABASE_URL" -f sql/deploy.sql
-- =============================================================================

-- 1. Password hashing (used by crypt()/gen_salt() in api/register.php)
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- 2. Base tables -------------------------------------------------------------

CREATE TABLE IF NOT EXISTS pelanggan (
    pelanggan_id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    alamat VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS kategori_menu (
    kategori_id SERIAL PRIMARY KEY,
    nama_kategori VARCHAR(30) NOT NULL
);

CREATE TABLE IF NOT EXISTS menu (
    menu_id SERIAL PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    harga NUMERIC(10,2) NOT NULL,
    stok INTEGER NOT NULL,
    status VARCHAR(30) NOT NULL,
    kategori_id INTEGER REFERENCES kategori_menu(kategori_id)
);

CREATE TABLE IF NOT EXISTS pesanan (
    pesanan_id SERIAL PRIMARY KEY,
    tanggal_pesanan TIMESTAMP NOT NULL,
    total_harga NUMERIC(10,2) NOT NULL,
    status_pesanan VARCHAR(30) NOT NULL,
    pelanggan_id INTEGER REFERENCES pelanggan(pelanggan_id)
);

CREATE TABLE IF NOT EXISTS detail_pesanan (
    detail_id SERIAL PRIMARY KEY,
    jumlah INTEGER NOT NULL,
    harga NUMERIC(10,2) NOT NULL,
    subtotal NUMERIC(10,2) NOT NULL,
    pesanan_id INTEGER REFERENCES pesanan(pesanan_id),
    menu_id INTEGER REFERENCES menu(menu_id)
);

CREATE TABLE IF NOT EXISTS pembayaran (
    pembayaran_id SERIAL PRIMARY KEY,
    metode_pembayaran VARCHAR(30) NOT NULL,
    jumlah_bayar NUMERIC(15,2) NOT NULL,
    tanggal_bayar TIMESTAMP NOT NULL,
    status_pembayaran INTEGER NOT NULL,
    pesanan_id INTEGER REFERENCES pesanan(pesanan_id)
);

CREATE TABLE IF NOT EXISTS bahan (
    bahan_id SERIAL PRIMARY KEY,
    nama_bahan VARCHAR(100) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    stok INTEGER NOT NULL,
    menu_id INTEGER REFERENCES menu(menu_id)
);

CREATE TABLE IF NOT EXISTS detail_bahan (
    detail_bahan_id SERIAL PRIMARY KEY,
    jumlah NUMERIC(10,2) NOT NULL,
    bahan_id INTEGER REFERENCES bahan(bahan_id)
);

-- 3. Accounts ----------------------------------------------------------------
-- pelanggan_id is nullable and unconstrained here: the app links a profile row
-- at registration (api/register.php) and backfills one at login if missing.

CREATE TABLE IF NOT EXISTS akun (
    akun_id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role VARCHAR(20) NOT NULL,
    pelanggan_id INTEGER UNIQUE REFERENCES pelanggan(pelanggan_id),

    CONSTRAINT cek_role CHECK (role IN ('pengguna', 'admin'))
);

-- 4. Profile columns (migration 001) -----------------------------------------

ALTER TABLE pelanggan ADD COLUMN IF NOT EXISTS email VARCHAR(255);
ALTER TABLE pelanggan ADD COLUMN IF NOT EXISTS jenis_kelamin VARCHAR(20);
ALTER TABLE pelanggan ADD COLUMN IF NOT EXISTS tanggal_lahir DATE;

-- 5. Product images + stock guard (migration 003) ----------------------------

ALTER TABLE menu ADD COLUMN IF NOT EXISTS gambar VARCHAR(255);

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'cek_menu_stok') THEN
        ALTER TABLE menu ADD CONSTRAINT cek_menu_stok CHECK (stok >= 0);
    END IF;
END $$;

-- 6. Session storage (migration 005) -----------------------------------------
-- Required on serverless: file-based sessions do not survive between requests.

CREATE TABLE IF NOT EXISTS php_sessions (
    id            TEXT PRIMARY KEY,
    data          TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS php_sessions_last_activity_idx
    ON php_sessions (last_activity);

-- 7. Catalog seed ------------------------------------------------------------

INSERT INTO kategori_menu (nama_kategori)
SELECT v.nama_kategori
FROM (VALUES ('Bakso'), ('Mie Ayam'), ('Minuman'), ('Tambahan')) AS v(nama_kategori)
WHERE NOT EXISTS (
    SELECT 1 FROM kategori_menu k WHERE k.nama_kategori = v.nama_kategori
);

INSERT INTO menu (menu_id, nama_menu, harga, stok, status, kategori_id, gambar)
SELECT v.menu_id, v.nama_menu, v.harga::numeric(10,2), 10, 'tersedia',
       k.kategori_id, v.gambar
FROM (VALUES
    ( 1, 'Bakso Solo Campur',    20000, 'Bakso',    '/assets/documentasi/image3.png'),
    ( 2, 'Bakso Solo Tanpa Tahu',20000, 'Bakso',    '/assets/documentasi/image4.png'),
    ( 3, 'Cilok 1 Porsi',        15000, 'Tambahan', '/assets/documentasi/image5.png'),
    ( 4, 'Mie Ayam Mozarella',   15000, 'Mie Ayam', '/assets/documentasi/image1.png'),
    ( 5, 'Mie Ayam Ceker',       15000, 'Mie Ayam', '/assets/documentasi/image6.png'),
    ( 6, 'Mie Ayam Original',    10000, 'Mie Ayam', '/assets/documentasi/image7.png'),
    ( 7, 'Air Mineral',           4000, 'Minuman',  '/assets/documentasi/image8.png'),
    ( 8, 'Es Jeruk',              5000, 'Minuman',  '/assets/documentasi/image9.png'),
    ( 9, 'Es Teh',                4000, 'Minuman',  '/assets/documentasi/image10.png'),
    (10, 'Ceker Ayam',            5000, 'Tambahan', '/assets/documentasi/image11.png'),
    (11, 'Pangsit Goreng',        5000, 'Tambahan', '/assets/documentasi/image12.png')
) AS v(menu_id, nama_menu, harga, nama_kategori, gambar)
JOIN kategori_menu k ON k.nama_kategori = v.nama_kategori
WHERE NOT EXISTS (SELECT 1 FROM menu m WHERE m.nama_menu = v.nama_menu);

SELECT setval(
    pg_get_serial_sequence('menu', 'menu_id'),
    (SELECT COALESCE(MAX(menu_id), 1) FROM menu)
);

-- 8. Admin account -----------------------------------------------------------
-- CHANGE THE PASSWORD before running this in production.
-- pelanggan_id stays NULL; api/login.php creates and links a profile row on
-- first sign-in, and Admin.php's profile gate then requires it to be filled.

INSERT INTO akun (username, password_hash, role, pelanggan_id)
VALUES ('admin', crypt('CHANGE_ME_BEFORE_DEPLOY', gen_salt('bf')), 'admin', NULL)
ON CONFLICT (username) DO NOTHING;

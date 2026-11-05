-- Migration 003: Seed the product catalog and enforce non-negative stock.
--
-- The `menu` table was empty while Menu.php hardcoded 11 product cards, so there
-- was nothing for admin stock management to operate on. This seeds the catalog
-- with fixed menu_id values so Menu.php can reference each product by id.
--
-- Idempotent: safe to run repeatedly.

-- 1. Product image path (used by the customer menu card).
ALTER TABLE menu ADD COLUMN IF NOT EXISTS gambar VARCHAR(255);

-- 2. Stock can never go negative.
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'cek_menu_stok'
    ) THEN
        ALTER TABLE menu ADD CONSTRAINT cek_menu_stok CHECK (stok >= 0);
    END IF;
END $$;

-- 3. Categories used by the existing cards.
INSERT INTO kategori_menu (nama_kategori)
SELECT v.nama_kategori
FROM (VALUES ('Bakso'), ('Mie Ayam'), ('Minuman'), ('Tambahan')) AS v(nama_kategori)
WHERE NOT EXISTS (
    SELECT 1 FROM kategori_menu k WHERE k.nama_kategori = v.nama_kategori
);

-- 4. Products. Prices match the visible "Rp ..." text on each Menu.php card.
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
WHERE NOT EXISTS (
    SELECT 1 FROM menu m WHERE m.nama_menu = v.nama_menu
);

-- 5. Keep the sequence ahead of the explicit ids above.
SELECT setval(
    pg_get_serial_sequence('menu', 'menu_id'),
    (SELECT COALESCE(MAX(menu_id), 1) FROM menu)
);
-- Migration 006: owner role, menu CRUD fields, order metadata.
--
-- Adds the fourth role from the PRD (§3) and the columns that menu management
-- and order persistence need. Safe to run twice.

-- 1. Role: pengguna | admin | owner ------------------------------------------

ALTER TABLE akun DROP CONSTRAINT IF EXISTS cek_role;

ALTER TABLE akun
    ADD CONSTRAINT cek_role
    CHECK (role IN ('pengguna', 'admin', 'owner'));

-- 2. Menu: owner-managed fields ---------------------------------------------

ALTER TABLE menu ADD COLUMN IF NOT EXISTS deskripsi VARCHAR(255);

-- Soft delete. A product that appears on a past order cannot be hard-deleted
-- without orphaning detail_pesanan, so deactivation is the fallback.
ALTER TABLE menu ADD COLUMN IF NOT EXISTS aktif BOOLEAN NOT NULL DEFAULT TRUE;

-- `status` is derived from stock; keep it consistent with the new flag.
UPDATE menu SET status = 'habis'
WHERE aktif = FALSE AND status <> 'habis';

-- 3. Order metadata ----------------------------------------------------------
-- `pesanan` had no place for the table, the order type, or free-text notes,
-- so a receipt could not be reproduced from the row alone.

ALTER TABLE pesanan ADD COLUMN IF NOT EXISTS meja VARCHAR(10);
ALTER TABLE pesanan ADD COLUMN IF NOT EXISTS jenis_pesanan VARCHAR(20);
ALTER TABLE pesanan ADD COLUMN IF NOT EXISTS catatan VARCHAR(500);
ALTER TABLE pesanan ADD COLUMN IF NOT EXISTS nama_pemesan VARCHAR(100);

-- Sales aggregation scans by day.
CREATE INDEX IF NOT EXISTS pesanan_tanggal_idx
    ON pesanan (tanggal_pesanan);

CREATE INDEX IF NOT EXISTS detail_pesanan_pesanan_idx
    ON detail_pesanan (pesanan_id);
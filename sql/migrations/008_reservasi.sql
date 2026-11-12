-- Migration 008: persisted table reservations.
--
-- Seats used to live only in sessionStorage, so nobody could see them and only
-- the browser that created one knew it existed. A table now stays occupied
-- until an admin releases it.

CREATE TABLE IF NOT EXISTS reservasi (
    reservasi_id   SERIAL PRIMARY KEY,
    meja           VARCHAR(10) NOT NULL,
    pelanggan_id   INTEGER REFERENCES pelanggan(pelanggan_id),
    nama_pemesan   VARCHAR(100) NOT NULL,
    jumlah_orang   INTEGER NOT NULL,
    tanggal        DATE NOT NULL,
    waktu          TIME NOT NULL,
    status         VARCHAR(20) NOT NULL DEFAULT 'aktif',
    dibuat_pada    TIMESTAMP NOT NULL DEFAULT NOW(),
    dirilis_pada   TIMESTAMP,

    CONSTRAINT cek_reservasi_status
        CHECK (status IN ('aktif', 'selesai')),

    CONSTRAINT cek_reservasi_orang
        CHECK (jumlah_orang BETWEEN 1 AND 20)
);

-- One active booking per table. A released row keeps its history, so the
-- partial index only constrains the rows that still hold the table.
CREATE UNIQUE INDEX IF NOT EXISTS reservasi_meja_aktif_idx
    ON reservasi (meja)
    WHERE status = 'aktif';

CREATE INDEX IF NOT EXISTS reservasi_status_idx
    ON reservasi (status, tanggal);
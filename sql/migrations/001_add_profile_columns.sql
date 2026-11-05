-- Migration 001: Add profile fields to pelanggan for Pe Be El
-- Applied: 2026-11-04

-- Email address (optional at schema level; validated in application)
ALTER TABLE pelanggan
    ADD COLUMN IF NOT EXISTS email VARCHAR(255);

-- Gender
ALTER TABLE pelanggan
    ADD COLUMN IF NOT EXISTS jenis_kelamin VARCHAR(20);

-- Birth date
ALTER TABLE pelanggan
    ADD COLUMN IF NOT EXISTS tanggal_lahir DATE;

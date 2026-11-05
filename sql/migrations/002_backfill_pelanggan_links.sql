-- Migration 002: Backfill missing pelanggan links for legacy accounts.
--
-- Accounts created before api/register.php started linking a pelanggan row
-- have akun.pelanggan_id = NULL. Without a linked row there is no place to
-- store profile data (nama, email, no_hp, jenis_kelamin, tanggal_lahir), so
-- /api/profile.php has nothing to read or write for those users.
--
-- This creates one blank pelanggan row per unlinked account and links it.

DO $$
DECLARE
    r      RECORD;
    new_id INTEGER;
BEGIN
    FOR r IN SELECT akun_id FROM akun WHERE pelanggan_id IS NULL ORDER BY akun_id
    LOOP
        INSERT INTO pelanggan (nama, no_hp, alamat)
        VALUES ('', '', '')
        RETURNING pelanggan_id INTO new_id;

        UPDATE akun SET pelanggan_id = new_id WHERE akun_id = r.akun_id;
    END LOOP;
END $$;

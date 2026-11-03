-- Web-auth setup for Pe Be El. Adapted from sql/login.sql (kept untouched).
--
-- fk_akun_pelanggan and cek_relasi_role are deferred because they depend on table
-- pelanggan from sql/iniSql.sql (future CRUD scope), and the current Register form
-- collects no nama/no_hp/alamat. pelanggan_id is kept as a nullable placeholder
-- for when CRUD is integrated.

-- Mengaktifkan ekstensi hashing password
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Membuat tabel akun
CREATE TABLE akun (
    akun_id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role VARCHAR(20) NOT NULL,
    pelanggan_id INTEGER UNIQUE, -- nullable until pelanggan/CRUD (iniSql.sql) is integrated

    CONSTRAINT cek_role
        CHECK (role IN ('pengguna', 'admin'))
);

-- Optional test admin (password: admin123)
-- INSERT INTO akun (username, password_hash, role)
-- VALUES ('admin', crypt('admin123', gen_salt('bf')), 'admin');

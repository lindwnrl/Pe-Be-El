-- Mengaktifkan ekstensi hashing password
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Membuat tabel akun
CREATE TABLE akun (
    akun_id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role VARCHAR(20) NOT NULL,
    pelanggan_id INTEGER UNIQUE,

    CONSTRAINT cek_role
        CHECK (role IN ('pengguna', 'admin')),

    CONSTRAINT fk_akun_pelanggan
        FOREIGN KEY (pelanggan_id)
        REFERENCES pelanggan(pelanggan_id),

    CONSTRAINT cek_relasi_role
        CHECK (
            (role = 'pengguna' AND pelanggan_id IS NOT NULL)
            OR
            (role = 'admin' AND pelanggan_id IS NULL)
        )
);
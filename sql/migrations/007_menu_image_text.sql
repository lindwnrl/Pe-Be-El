-- Migration 007: allow uploaded menu images.
--
-- The owner form can now accept a dropped/selected image, which is downscaled
-- in the browser and stored as a data URL. VARCHAR(255) cannot hold that.

ALTER TABLE menu ALTER COLUMN gambar TYPE TEXT;
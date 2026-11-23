CREATE DATABASE IF NOT EXISTS `project_sewa_mobil` CHARACTER
SET
  utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `project_sewa_mobil`;

-- Users table for login/register
CREATE TABLE
  IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(30) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM ('user', 'admin') NOT NULL DEFAULT 'user',
    `verification_status` ENUM ('unverified', 'pending', 'verified', 'rejected') NOT NULL DEFAULT 'unverified',
    `reject_reason` TEXT NULL DEFAULT NULL,
    `ktp_file` VARCHAR(255) NULL,
    `sim_file` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Cars table for homepage listings
CREATE TABLE
  IF NOT EXISTS `cars` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `make` VARCHAR(100) NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `year` YEAR NULL,
    `category` VARCHAR(50) NULL,
    `fuel_type` VARCHAR(50) NULL,
    `seats` INT NULL,
    `stock` INT NOT NULL DEFAULT 1,
    `price_per_day` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `image` VARCHAR(255) NULL,
    `available` TINYINT (1) NOT NULL DEFAULT 1,
    `is_type` TINYINT(1) NOT NULL DEFAULT 0,
    `type_key` VARCHAR(255) NULL,
    `number_plate` VARCHAR(50) NULL,
    `chassis_number` VARCHAR(255) NULL,
    `webhook_alarm` VARCHAR(255) NULL,
    `webhook_mesin` VARCHAR(255) NULL,
    `engine_capacity` VARCHAR(50) NULL,
    `transmission` VARCHAR(50) DEFAULT 'Manual',
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Bookings table
CREATE TABLE
  IF NOT EXISTS `bookings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `car_id` INT UNSIGNED NOT NULL,
    `assigned_car_id` INT UNSIGNED NULL,
    `pickup_location` VARCHAR(255) NOT NULL,
    `return_location` VARCHAR(255) NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `address` TEXT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `addon_driver` TINYINT (1) NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `total_price` DECIMAL(12, 2) NOT NULL,
    `additional_cost` DECIMAL(12, 2) DEFAULT 0.00,
    `damage_image` VARCHAR(255) NULL,
    `damage_description` TEXT NULL,
    `payment_token` VARCHAR(255) DEFAULT NULL,
    `payment_url` VARCHAR(255) DEFAULT NULL,
    `payment_expiry` TIMESTAMP NULL DEFAULT NULL,
    `midtrans_order_id` VARCHAR(100) DEFAULT NULL,
    `status` ENUM ('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Data armada (50 mobil) TIDAK di-insert di sini.
-- Sumber tunggal ada di db/cars.json dan dimuat oleh db/seed_cars.php
-- (otomatis saat include/db_config.php mendeteksi tabel cars kosong).

UPDATE `cars` SET `type_key` = CONCAT(COALESCE(`make`,''),'|',COALESCE(`model`,''),'|',COALESCE(`year`,''),'|',COALESCE(`fuel_type`,''),'|',COALESCE(`engine_capacity`,''),'|',COALESCE(`seats`,''),'|',COALESCE(`transmission`,'Manual'),'|',COALESCE(`category`,''),'|',COALESCE(`price_per_day`,'')) WHERE `type_key` IS NULL OR `type_key` = '';

-- Admins table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('superuser', 'staff') NOT NULL DEFAULT 'staff',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT IGNORE INTO `admins` (`nama`, `email`, `password`, `role`) VALUES
  ('Ahmad Fauzi', 'ahmad@sewamobilsby.id', 'admin123', 'superuser');

-- Business Settings table
CREATE TABLE IF NOT EXISTS `business_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT IGNORE INTO `business_settings` (`setting_key`, `setting_value`) VALUES
  ('company_name', 'SewaMobil'),
  ('phone', '08123456789'),
  ('social_media', '@sewamobilsby'),
  ('address', 'Jl. Ketintang Baru, Surabaya, Jawa Timur');
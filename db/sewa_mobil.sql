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
    `price_per_day` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `image` VARCHAR(255) NULL,
    `available` TINYINT (1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Bookings table
CREATE TABLE
  IF NOT EXISTS `bookings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `car_id` INT UNSIGNED NOT NULL,
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
    `status` ENUM ('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Insert cars data
INSERT IGNORE INTO `cars` (
  `id`,
  `make`,
  `model`,
  `year`,
  `category`,
  `fuel_type`,
  `seats`,
  `price_per_day`,
  `image`,
  `available`
)
VALUES
  (
    1,
    'Toyota',
    'Avanza',
    2024,
    'MPV',
    'Bensin',
    7,
    350000.00,
    'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop',
    1
  ),
  (
    2,
    'Mitsubishi',
    'Pajero Sport',
    2023,
    'SUV',
    'Diesel',
    7,
    750000.00,
    'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop',
    1
  ),
  (
    3,
    'Honda',
    'Civic',
    2023,
    'Sedan',
    'Bensin',
    5,
    500000.00,
    'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop',
    1
  ),
  (
    4,
    'Hyundai',
    'Ioniq 5',
    2024,
    'EV',
    'Listrik',
    5,
    900000.00,
    'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop',
    1
  ),
  (
    5,
    'Suzuki',
    'Ertiga',
    2022,
    'MPV',
    'Bensin',
    7,
    300000.00,
    'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop',
    1
  ),
  (
    6,
    'Toyota',
    'Fortuner',
    2024,
    'SUV',
    'Diesel',
    7,
    800000.00,
    'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop',
    1
  );
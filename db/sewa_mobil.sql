CREATE DATABASE IF NOT EXISTS `sewa_mobil` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sewa_mobil`;

-- Users table for login/register
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cars table for homepage listings (optional)
CREATE TABLE `cars` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `make` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `year` YEAR NULL,
  `price_per_day` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image` VARCHAR(255) NULL,
  `available` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example insert (uncomment to use)
-- INSERT INTO `cars` (`make`, `model`, `year`, `price_per_day`, `image`, `available`) VALUES
-- ('Toyota', 'Avanza', 2018, 350000, 'assets/images/avanza.jpg', 1);

-- Note: create admin account using the app's register form or insert manually with a bcrypt hash for `password`.

-- Crop irrigation tables.
-- Create the database first (phpMyAdmin, then import.
-- Importing again drops these tables and loads the demo rows.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS harvests;
DROP TABLE IF EXISTS fertilizers;
DROP TABLE IF EXISTS irrigations;
DROP TABLE IF EXISTS crops;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE crops (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  crop_name VARCHAR(100) NOT NULL,
  variety VARCHAR(100) NOT NULL,
  season ENUM('Rainy', 'Winter', 'Summer') NOT NULL,
  area DECIMAL(10,2) NOT NULL,
  area_unit ENUM('hectare', 'acre', 'ropani') NOT NULL,
  planting_date DATE NOT NULL,
  expected_harvest_date DATE NULL,
  status ENUM('Growing', 'Harvested') NOT NULL DEFAULT 'Growing',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE irrigations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  crop_id INT UNSIGNED NOT NULL,
  schedule_date DATE NOT NULL,
  water_amount DECIMAL(10,2) NOT NULL,
  method ENUM('Drip', 'Sprinkler', 'Flood', 'Manual') NOT NULL,
  status ENUM('Scheduled', 'Completed') NOT NULL DEFAULT 'Scheduled',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_irrigations_crop FOREIGN KEY (crop_id) REFERENCES crops (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fertilizers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  crop_id INT UNSIGNED NOT NULL,
  fertilizer_name VARCHAR(100) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  unit ENUM('kg', 'g', 'liter') NOT NULL,
  applied_date DATE NOT NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_fertilizers_crop FOREIGN KEY (crop_id) REFERENCES crops (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE harvests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  crop_id INT UNSIGNED NOT NULL,
  harvest_date DATE NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  unit ENUM('kg', 'quintal', 'ton') NOT NULL,
  quality ENUM('Good', 'Average', 'Poor') NOT NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_harvests_crop FOREIGN KEY (crop_id) REFERENCES crops (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activities (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  action VARCHAR(50) NOT NULL,
  details VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_activities_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, username, full_name, password_hash) VALUES
(1, 'admin', 'Admin User', '$2y$12$w9/XrGlISCgirqmVJSTHheul8Y6poF1qQeZmRflQV0WXpTrj2aGZ6'),
(2, 'farmer', 'Farm User', '$2y$12$ToCldrkZEQQsz.hYx3EFa.G.R1gYT/ko5cC7MLJaydzt08BMyaieC');

INSERT INTO crops (id, crop_name, variety, season, area, area_unit, planting_date, expected_harvest_date, status, notes) VALUES
(1, 'Rice', 'Basmati', 'Rainy', 1.50, 'hectare', '2026-06-15', '2026-11-01', 'Growing', 'Main field near the canal.'),
(2, 'Wheat', 'HD-2967', 'Winter', 2.00, 'acre', '2025-11-20', '2026-04-10', 'Growing', ''),
(3, 'Maize', 'Local Yellow', 'Summer', 8.00, 'ropani', '2026-03-01', '2026-07-15', 'Harvested', 'Lower terrace.');

INSERT INTO irrigations (crop_id, schedule_date, water_amount, method, status, notes) VALUES
(1, '2026-07-01', 500.00, 'Drip', 'Completed', 'Morning round'),
(1, '2026-07-10', 450.00, 'Drip', 'Scheduled', ''),
(2, '2025-12-05', 800.00, 'Flood', 'Completed', ''),
(3, '2026-04-02', 300.00, 'Manual', 'Completed', 'Used the small pump');

INSERT INTO fertilizers (crop_id, fertilizer_name, quantity, unit, applied_date, notes) VALUES
(1, 'Urea', 25.00, 'kg', '2026-07-05', ''),
(2, 'Compost', 40.00, 'kg', '2025-12-01', 'From the farm pit'),
(3, 'DAP', 10.00, 'kg', '2026-03-20', '');

INSERT INTO harvests (crop_id, harvest_date, quantity, unit, quality, notes) VALUES
(3, '2026-07-20', 180.00, 'kg', 'Good', 'Maize cobs were dried for two days before weighing. Some plants on the lower side of the field were smaller, so the total is a bit less than expected.');

INSERT INTO activities (user_id, action, details, created_at) VALUES
(1, 'Added crop', 'Rice', '2026-06-15 08:30:00'),
(1, 'Added crop', 'Wheat', '2025-11-20 10:00:00'),
(1, 'Added crop', 'Maize', '2026-03-01 09:15:00'),
(1, 'Added irrigation', 'Rice on 2026-07-01', '2026-07-01 07:00:00'),
(1, 'Added irrigation', 'Wheat on 2025-12-05', '2025-12-05 07:30:00'),
(1, 'Added fertilizer', 'Urea for Rice', '2026-07-05 11:00:00'),
(1, 'Recorded harvest', 'Maize', '2026-07-20 16:00:00');

-- ============================================================
-- БД сервиса «Сладкие сны»
-- ============================================================
CREATE DATABASE IF NOT EXISTS sladkie_sny
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sladkie_sny;

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    login         VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('guest','admin') NOT NULL DEFAULT 'guest',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE room_types (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    code          ENUM('standard','studio','lux') NOT NULL UNIQUE,
    title         VARCHAR(50)   NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    capacity      TINYINT UNSIGNED NOT NULL,
    area          DECIMAL(5,2)  NOT NULL,
    has_ac        BOOLEAN NOT NULL DEFAULT FALSE,
    has_tv        BOOLEAN NOT NULL DEFAULT FALSE,
    has_fridge    BOOLEAN NOT NULL DEFAULT FALSE,
    has_wifi      BOOLEAN NOT NULL DEFAULT FALSE,
    image_path    VARCHAR(255) NOT NULL DEFAULT 'img/standart.png'
) ENGINE=InnoDB;

CREATE TABLE rooms (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    type_id     INT NOT NULL,
    floor       TINYINT UNSIGNED DEFAULT NULL,
    FOREIGN KEY (type_id) REFERENCES room_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    room_id     INT NOT NULL,
    first_name  VARCHAR(100) NOT NULL,
    last_name   VARCHAR(100) NOT NULL,
    phone       VARCHAR(20)  NOT NULL,
    email       VARCHAR(100) NOT NULL,
    check_in    DATE NOT NULL,
    check_out   DATE NOT NULL,
    status      ENUM('new','approved','rejected') NOT NULL DEFAULT 'new',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_dates  (check_in, check_out)
) ENGINE=InnoDB;

-- ============================================================
-- Демо-данные
-- admin / admin123  (СМЕНИТЕ ХЭШ!)
-- ============================================================
INSERT INTO users (login, password_hash, role) VALUES
('admin',
 '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdBqSfR0f3P7hE3K6',
 'admin');

INSERT INTO room_types
(code, title, price_per_day, capacity, area, has_ac, has_tv, has_fridge, has_wifi, image_path) VALUES
('standard', 'Стандарт', 10000.00, 1, 18.00, 0, 0, 0, 1, 'img/standart.png'),
('studio',   'Студия',    8000.00, 2, 28.00, 1, 1, 0, 1, 'img/studio.jpg'),
('lux',      'Люкс',     19000.00, 3, 55.00, 1, 1, 1, 1, 'img/lux.png');

INSERT INTO rooms (room_number, type_id, floor) VALUES
('101', 1, 1), ('102', 1, 1), ('103', 1, 2),
('201', 2, 2), ('202', 2, 2), ('203', 2, 3),
('301', 3, 3), ('302', 3, 3), ('303', 3, 4);

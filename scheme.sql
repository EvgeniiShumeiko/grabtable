CREATE DATABASE IF NOT EXISTS grabtable
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE grabtable;

CREATE TABLE IF NOT EXISTS users
(
    user_id     INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    login       VARCHAR(15) UNIQUE NOT NULL,
    password    VARCHAR(256)       NOT NULL,
    super_admin tinyint(1) DEFAULT 0,
    date_create TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    INDEX login_password (login, password)
);

CREATE TABLE IF NOT EXISTS places
(
    place_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL,
    image_url VARCHAR(512) NOT NULL,
    phone VARCHAR(128) NOT NULL,
    middle_check SMALLINT,
    clocks JSON NOT NULL,
    address TEXT NOT NULL,
    admin_ids VARCHAR(256) NOT NULL,
    map TEXT NOT NULL,
    tables JSON NOT NULL,
    date_create TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    INDEX place_name (name, place_id)
);

CREATE TABLE IF NOT EXISTS booking
(
    booking_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    place_id INT UNSIGNED NOT NULL,
    table_id INT UNSIGNED NOT NULL,
    client_name VARCHAR(128) NOT NULL,
    client_phone VARCHAR(128) NOT NULL,
    client_comment TEXT,
    date_booking DATETIME NOT NULL,
    date_create TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX place_booking (place_id, date_booking),
    FOREIGN KEY (place_id) REFERENCES places(place_id)
);

CREATE TABLE IF NOT EXISTS tokens
(
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    access_token varchar(32) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    active TINYINT(1) DEFAULT 1,
    INDEX user_token (user_id, access_token, active),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);


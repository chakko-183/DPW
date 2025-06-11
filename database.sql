-- Database schema untuk website top-up game
CREATE DATABASE IF NOT EXISTS topup_game;
USE topup_game;

-- Tabel users untuk login
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel games
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel diamond packages
CREATE TABLE diamond_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT,
    amount INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Tabel transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    server VARCHAR(255) NOT NULL,
    game_id INT,
    diamond_amount INT NOT NULL,
    payment_method VARCHAR(100) NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (email, password, role) VALUES 
('admin@topupgame.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default games
INSERT INTO games (name, image, description) VALUES 
('Mobile Legends', 'gambar/MobileLegends.jpg', 'Game MOBA (Multiplayer Online Battle Arena) yang populer di Indonesia dan di seluruh dunia.'),
('PUBG Mobile', 'gambar/PUBG.jpg', 'Game battle royale online multipemain di mana hingga 100 pemain bertempur dalam pertandingan last man standing.'),
('Free Fire', 'gambar/Free_Fire.webp', 'Game battle royale seru dan penuh aksi, di mana 50 pemain bertarung di satu pulau hingga hanya satu yang bertahan.'),
('Genshin Impact', 'gambar/GenshinImpact.jpg', 'Game aksi role-playing (RPG) dunia terbuka yang dikembangkan oleh HoYoverse.');

-- Insert diamond packages
INSERT INTO diamond_packages (game_id, amount, price) VALUES 
(1, 50, 15000),
(1, 100, 25000),
(1, 200, 45000),
(1, 250, 55000),
(2, 50, 15000),
(2, 100, 25000),
(2, 200, 45000),
(2, 250, 55000),
(3, 50, 15000),
(3, 100, 25000),
(3, 200, 45000),
(3, 250, 55000),
(4, 50, 15000),
(4, 100, 25000),
(4, 200, 45000),
(4, 250, 55000);

-- Insert sample transactions
INSERT INTO transactions (user_id, server, game_id, diamond_amount, payment_method, status, total_price) VALUES 
('123123', '123123', 1, 100, 'DANA', 'success', 25000),
('456456', '456456', 2, 50, 'GoPay', 'success', 15000),
('789789', '789789', 3, 140, 'OVO', 'failed', 35000);

-- Tambahkan kolom reset_token dan reset_token_expiry ke tabel users
ALTER TABLE users
ADD COLUMN reset_token VARCHAR(255) NULL AFTER password,
ADD COLUMN reset_token_expiry DATETIME NULL AFTER reset_token;

-- Tabel reviews
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT, -- Jika Anda ingin mengaitkan ulasan dengan user yang login
    username VARCHAR(255) NOT NULL, -- Nama user, bisa diambil dari sesi atau input
    rating INT NOT NULL, -- Rating dari 1 sampai 5
    review_text TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending', -- Status ulasan (menunggu, disetujui, ditolak)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL -- Opsional: Jika user_id null saat user dihapus
);
-- Tabel wishlist
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, game_id), -- Memastikan satu user hanya bisa punya satu game di wishlist
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE

    
);

-- Tabel notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- NULL jika notifikasi untuk semua user, NOT NULL jika spesifik
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE -- Opsional: Jika user dihapus, notifikasi spesifik juga dihapus
);

-- Contoh notifikasi untuk semua user (tinggalkan user_id NULL)
INSERT INTO notifications (user_id, message) VALUES (NULL, 'Selamat datang di layanan top up game kami! Nikmati diskon spesial untuk top up pertama Anda.');
INSERT INTO notifications (user_id, message) VALUES (NULL, 'Server Mobile Legends sedang dalam pemeliharaan. Top up akan aktif kembali pukul 18:00 WIB.');

-- Contoh notifikasi spesifik user (ganti 1 dengan ID user yang ada)
-- INSERT INTO notifications (user_id, message) VALUES (1, 'Top up 500 diamond Mobile Legends Anda telah berhasil diproses!');
ALTER TABLE users
ADD COLUMN reset_token VARCHAR(255) NULL AFTER password,
ADD COLUMN reset_token_expiry DATETIME NULL AFTER reset_token;               
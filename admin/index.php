<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$db->query('SELECT COUNT(*) as total FROM games WHERE status = "active"');
$total_games = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM transactions');
$total_transactions = $db->single()['total'];

$db->query('SELECT SUM(total_price) as total FROM transactions WHERE status = "success"');
$total_revenue = $db->single()['total'] ?? 0;

$db->query('SELECT COUNT(*) as total FROM transactions WHERE status = "success"');
$success_transactions = $db->single()['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Dashboard Admin</h1>
        <nav>
            <!-- <a href="index.php">Dashboard</a> -->
            <a href="games.php">Games</a>
            <a href="transactions.php">Transaksi</a>
            <a href="packages.php">Paket Diamond</a>
            <a href="reviews.php">Ulasan</a>
            <a href="notifications.php">Notifikasi</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="admin-container fade-in">
        <section class="admin-stats">
            <h2>Statistik Website</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_games; ?></div>
                    <div>Total Game Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_transactions; ?></div>
                    <div>Total Transaksi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $success_transactions; ?></div>
                    <div>Transaksi Sukses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
                    <div>Total Pendapatan</div>
                </div>
            </div>
        </section>

        <section class="admin-actions">
            <h2>Aksi Cepat</h2>
            <div class="admin-nav">
                <a href="games.php"><i class="fas fa-gamepad"></i> Kelola Games</a>
                <a href="transactions.php"><i class="fas fa-receipt"></i> Kelola Transaksi</a>
                <a href="packages.php"><i class="fas fa-gem"></i> Kelola Paket Diamond</a>
                <a href="reviews.php"><i class="fas fa-star"></i> Kelola Ulasan</a>
                <a href="notifications.php"><i class="fas fa-bell"></i> Kelola Notifikasi</a>
            </div>
        </section>

        <section class="welcome-message">
            <div style="background: #3e3e50; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                <h3>Selamat datang, <?php echo htmlspecialchars($_SESSION['email']); ?>!</h3>
                <p>Anda login sebagai Administrator. Gunakan menu di atas untuk mengelola website Top Up Game Anda.</p>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-left">
            <p>© 2025 Kelompok 1 - Teknik Informatika</p>
        </div>
    </footer>
    <!-- <footer>
        <div class="footer-section">
            <h4>Tentang Kami</h4>
            <div class="footer-content">
                <p>© 2025 Top Up Game. All rights reserved.</p>
                <p>Website ini dibuat untuk memenuhi tugas akhir mata kuliah Dasar Pemrograman Web.</p>
            </div>
        </div>

        <div class="footer-section">
            <h4>Bantuan & Informasi</h4>
            <a href="#" class="footer-link">🛠 Bantuan & Masalah</a>
            <a href="#" class="footer-link">Halaman FAQ</a>
        </div>

        <div class="footer-section">
            <h4>Temukan kami di:</h4>
            <div class="social-icons">
                <a href="https://www.facebook.com/share/12MKyt8ynd2/?mibextid=wwXIfr" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/anggerme19/" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@aang_1905?_t=ZS-8wlMcWKkxiT&_r=1" target="_blank"><i class="fab fa-tiktok"></i></a>
            </div>
            <label for="pilih-negara">🌍 Pilih Negara:</label>
            <select id="pilih-negara" name="negara">
                <option value="id">🇮🇩 Indonesia</option>
                <option value="us">🇺🇸 Amerika Serikat</option>
                <option value="gb">🇬🇧 Inggris</option>
                <option value="jp">🇯🇵 Jepang</option>
                <option value="fr">🇫🇷 Prancis</option>
                <option value="de">🇩🇪 Jerman</option>
                <option value="kr">🇰🇷 Korea Selatan</option>
                <option value="cn">🇨🇳 Tiongkok</option>
                <option value="in">🇮🇳 India</option>
                <option value="au">🇦🇺 Australia</option>
            </select>
        </div>
    </footer> -->
    <script src="../script.js"></script>
</body>

</html>
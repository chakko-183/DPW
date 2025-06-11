<?php
session_start();
require_once 'config/database.php';

// Get transactions with game names
$db->query('SELECT t.*, g.name as game_name
            FROM transactions t
            LEFT JOIN games g ON t.game_id = g.id
            ORDER BY t.created_at DESC
            LIMIT 20');
$transactions = $db->resultset();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Riwayat Transaksi</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <header>
        <h1 class="fade-in">Riwayat Transaksi Anda</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="daftar-game.php">Daftar Game</a>
            <a href="riwayat.php">Riwayat</a>
            <a href="ulasan.php">Ulasan</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin/index.php">Admin Panel</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="loading-container">Loading</div>

    <main class="fade-in">
        <section class="history-section">
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="alert alert-success">
                    Transaksi berhasil diproses!
                </div>
            <?php elseif (isset($_GET['success']) && $_GET['success'] == 0): ?>
                <div class="alert alert-danger">
                    Terjadi kesalahan saat memproses transaksi.
                </div>
            <?php endif; ?>

            <h2>Daftar Transaksi Terakhir</h2>
            <?php if (empty($transactions)): ?>
                <p class="no-records">Belum ada transaksi yang tercatat.</p>
            <?php else: ?>
                <ul class="history-list">
                    <?php foreach ($transactions as $transaction): ?>
                        <li>
                            <div class="transaction-detail">
                                <strong>Game:</strong> <?php echo htmlspecialchars($transaction['game_name']); ?><br>
                                <strong>Jumlah:</strong> <?php echo htmlspecialchars($transaction['diamond_amount']); ?> Diamonds<br>
                                <strong>Harga:</strong> Rp <?php echo number_format($transaction['total_price'], 0, ',', '.'); ?><br>
                                <strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($transaction['payment_method']); ?><br>
                                <strong>Status:</strong>
                                <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                    <?php echo ucfirst($transaction['status']); ?>
                                </span><br>
                                <strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-left">
            <p>© 2025 Kelompok 1 - Teknik Informatika</p>
        </div>

        <div class="footer-center">
            <a href="bantuan_masalah.php" class="footer-link">🛠 Bantuan & Masalah</a>
            <a href="faq.html" class="footer-link">Halaman FAQ</a>
        </div>

        <div class="footer-right">
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
        <h4>Temukan kami di:</h4>
        <div class="social-icons">
            <a href="https://www.facebook.com/share/12MKyt8ynd2/?mibextid=wwXIfr" target="_blank"><i
                    class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/anggerme19/" target="_blank"><i class="fab fa-instagram"></i></a>
            <!-- <a href="https://twitter.com" target="_blank"
            ><i class="fab fa-twitter"></i
          ></a> -->
            <a href="https://www.tiktok.com/@aang_1905?_t=ZS-8wlMcWKkxiT&_r=1" target="_blank"><i
                    class="fab fa-tiktok"></i></a>
        </div>
                </div>
    </footer>
    <script src="script.js"></script>
</body>

</html>
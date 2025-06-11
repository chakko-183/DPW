<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['topup_data'])) {
    header('Location: daftar-game.php');
    exit();
}

$data = $_SESSION['topup_data'];

// Get unread notification count for the current user
$unread_notifications_count = 0;
if (isset($_SESSION['user_id'])) {
    $db->query('SELECT COUNT(*) AS unread_count FROM notifications WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = FALSE');
    $db->bind(':user_id', $_SESSION['user_id']);
    $unread_notifications_count = $db->single()['unread_count'];
} else {
    $db->query('SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id IS NULL AND is_read = FALSE');
    $unread_notifications_count = $db->single()['unread_count'];
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db->query('INSERT INTO transactions (user_id, server, game_id, diamond_amount, payment_method, status, total_price)
                VALUES (:user_id, :server, :game_id, :diamond_amount, :payment_method, :status, :total_price)');

    $db->bind(':user_id', $data['userid']);
    $db->bind(':server', $data['server']);
    $db->bind(':game_id', $data['game_id']);
    $db->bind(':diamond_amount', $data['diamond_amount']);
    $db->bind(':payment_method', $data['payment_method']);
    $db->bind(':status', 'success');
    $db->bind(':total_price', $data['price']);

    if ($db->execute()) {
        unset($_SESSION['topup_data']);
        header('Location: riwayat.php?success=1');
        exit();
    } else {
        header('Location: riwayat.php?success=0');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Konfirmasi & Checkout</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Konfirmasi & Checkout</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="daftar-game.php">Daftar Game</a>
            <a href="riwayat.php">Riwayat</a>
            <a href="ulasan.php">Ulasan</a>
            <a href="wishlist.php">Wishlist</a>
            <div class="notification-icon-wrapper">
                <a href="notifications.php" title="Notifikasi Anda">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_notifications_count > 0): ?>
                        <span class="notification-badge"><?php echo $unread_notifications_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin/index.php">Admin Panel</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="fade-in">
        <section class="summary-section">
            <h2>Ringkasan Pemesanan Anda</h2>
            <div class="summary">
                <p><strong>Game:</strong> <?php echo htmlspecialchars($data['game_name']); ?></p>
                <p><strong>ID Pengguna:</strong> <?php echo htmlspecialchars($data['userid']); ?></p>
                <p><strong>Server:</strong> <?php echo htmlspecialchars($data['server']); ?></p>
                <p><strong>Jumlah Top Up:</strong> <?php echo htmlspecialchars($data['diamond_amount']); ?> Diamonds</p>
                <p><strong>Harga:</strong> Rp <?php echo number_format($data['price'], 0, ',', '.'); ?></p>
                <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($data['payment_method']); ?></p>
            </div>

            <form method="POST">
                <button type="submit" class="btn-primary">Selesaikan & Bayar</button>
            </form>
        </section>
    </main>

    <footer>
        <div class="footer-section">
            <h4>Tentang Kami</h4>
            <div class="footer-content">
                <p>© 2025 Top Up Game. All rights reserved.</p>
            </div>
        </div>

        <div class="footer-section">
            <h4>Bantuan & Informasi</h4>
            <a href="bantuan_masalah.php" class="footer-link">🛠 Bantuan & Masalah</a>
            <a href="faq.html" class="footer-link">Halaman FAQ</a>
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
    </footer>
    <script src="script.js"></script>
</body>

</html>
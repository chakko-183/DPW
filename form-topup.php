<?php
session_start();
require_once 'config/database.php';

$game_id = $_GET['game_id'] ?? 1;

// Get game details
$db->query('SELECT * FROM games WHERE id = :id');
$db->bind(':id', $game_id);
$game = $db->single();

// Get diamond packages for this game
$db->query('SELECT * FROM diamond_packages WHERE game_id = :game_id AND status = "active"');
$db->bind(':game_id', $game_id);
$packages = $db->resultset();

// Get unread notification count for the current user
$unread_notifications_count = 0;
if (isset($_SESSION['user_id'])) {
    $db->query('SELECT COUNT(*) AS unread_count FROM notifications WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = FALSE');
    $db->bind(':user_id', $_SESSION['user_id']);
    $unread_notifications_count = $db->single()['unread_count'];
} else {
    // If not logged in, show general notifications (user_id IS NULL)
    $db->query('SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id IS NULL AND is_read = FALSE');
    $unread_notifications_count = $db->single()['unread_count'];
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_POST['userid'];
    $server = $_POST['server'];
    $package_id = $_POST['jumlah'];

    // Get package details
    $db->query('SELECT * FROM diamond_packages WHERE id = :id');
    $db->bind(':id', $package_id);
    $package = $db->single();

    // Store in session for next step
    $_SESSION['topup_data'] = [
        'userid' => $userid,
        'server' => $server,
        'game_id' => $game_id,
        'game_name' => $game['name'],
        'package_id' => $package_id,
        'diamond_amount' => $package['amount'],
        'price' => $package['price']
    ];

    header('Location: metode-pembayaran.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Form Top Up - <?php echo $game['name']; ?></title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Form Top Up - <?php echo $game['name']; ?></h1>
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
        <section class="form-section">
            <h2>Isi Detail Top Up</h2>
            <form class="topup-form" method="POST">
                <div class="input-group">
                    <label for="userid">User ID:</label>
                    <input type="number" id="userid" name="userid" placeholder="Masukkan User ID Anda" required />
                </div>

                <div class="input-group">
                    <label for="server">Server:</label>
                    <input type="text" id="server" name="server" placeholder="Masukkan Server Anda" required />
                </div>

                <div class="input-group">
                    <label for="jumlah">Jumlah Top Up:</label>
                    <select id="jumlah" name="jumlah" required>
                        <?php foreach ($packages as $package): ?>
                            <option value="<?php echo $package['id']; ?>">
                                <?php echo $package['amount']; ?> Diamonds - Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Lanjut Pembayaran</button>
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
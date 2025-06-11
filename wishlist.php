<?php
session_start();
require_once 'config/database.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?message=Anda harus login untuk melihat wishlist.&type=danger');
    exit();
}

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


// Fetch wishlist items for the current user
$wishlist_items = [];
try {
    $db->query('SELECT w.id as wishlist_id, g.id as game_id, g.name as game_name, g.image, g.description
                FROM wishlist w
                JOIN games g ON w.game_id = g.id
                WHERE w.user_id = :user_id
                ORDER BY w.created_at DESC');
    $db->bind(':user_id', $_SESSION['user_id']);
    $wishlist_items = $db->resultset();
} catch (PDOException $e) {
    error_log("Error fetching wishlist: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat wishlist.";
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Anda</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Wishlist Anda</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="daftar-game.php">Daftar Game</a>
            <a href="riwayat.php">Riwayat</a>
            <a href="ulasan.php">Ulasan</a>
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
        <section class="wishlist-section">
            <h2>Game yang Tersimpan di Wishlist</h2>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (empty($wishlist_items)): ?>
                <p class="no-records">Wishlist Anda kosong. Tambahkan game dari <a href="daftar-game.php" class="alert-link">Daftar Game</a>.</p>
            <?php else: ?>
                <div class="wishlist-list">
                    <?php foreach ($wishlist_items as $item): ?>
                        <div class="wishlist-item" data-wishlist-id="<?php echo $item['wishlist_id']; ?>" data-game-id="<?php echo $item['game_id']; ?>">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['game_name']); ?>">
                            <div class="wishlist-details">
                                <h3><?php echo htmlspecialchars($item['game_name']); ?></h3>
                                <p><?php echo substr(htmlspecialchars($item['description']), 0, 100); ?>...</p>
                            </div>
                            <div class="wishlist-actions">
                                <a href="form-topup.php?game_id=<?php echo $item['game_id']; ?>" class="btn btn-primary btn-small">Top Up Sekarang</a>
                                <!-- <button class="btn btn-danger btn-small remove-from-wishlist" data-game-id="<?php echo $item['game_id']; ?>">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button> -->
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
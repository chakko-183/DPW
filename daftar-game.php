<?php
session_start();
require_once 'config/database.php';

$db->query('SELECT * FROM games WHERE status = "active" ORDER BY name');
$games = $db->resultset();

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

$user_wishlist_game_ids = [];
if (isset($_SESSION['user_id'])) {
    $db->query('SELECT game_id FROM wishlist WHERE user_id = :user_id');
    $db->bind(':user_id', $_SESSION['user_id']);
    $wishlist_raw = $db->resultset();
    $user_wishlist_game_ids = array_column($wishlist_raw, 'game_id');
}

// Handle AJAX requests for wishlist (add/remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['game_id'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Anda harus login untuk mengelola wishlist.']);
        exit();
    }

    $game_id = filter_var($_POST['game_id'], FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if (!$game_id) {
        echo json_encode(['success' => false, 'message' => 'ID game tidak valid.']);
        exit();
    }

    if ($_POST['action'] === 'add_to_wishlist') {
        try {
            $db->query('INSERT INTO wishlist (user_id, game_id) VALUES (:user_id, :game_id)');
            $db->bind(':user_id', $user_id);
            $db->bind(':game_id', $game_id);
            $db->execute();
            echo json_encode(['success' => true, 'message' => 'Game ditambahkan ke wishlist!', 'status' => 'added']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['success' => false, 'message' => 'Game sudah ada di wishlist Anda.']);
            } else {
                error_log("Add to wishlist error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan ke wishlist.']);
            }
        }
    } elseif ($_POST['action'] === 'remove_from_wishlist') {
        $db->query('DELETE FROM wishlist WHERE user_id = :user_id AND game_id = :game_id');
        $db->bind(':user_id', $user_id);
        $db->bind(':game_id', $game_id);
        if ($db->execute()) {
            echo json_encode(['success' => true, 'message' => 'Game dihapus dari wishlist.', 'status' => 'removed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari wishlist.']);
        }
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Game</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <header>
        <h1 class="fade-in">Daftar Game Populer</h1>
        <nav>
            <a href="index.php">Beranda</a>
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

    <div class="loading-container">Loading</div>

    <main class="fade-in">
        <section class="game-selection-section">
            <h2>Pilih Game Favoritmu</h2>
            <div class="game-list">
                <?php foreach ($games as $game): ?>
                    <div class="card">
                        <a href="form-topup.php?game_id=<?php echo $game['id']; ?>">
                            <img src="<?php echo htmlspecialchars($game['image']); ?>" alt="<?php echo htmlspecialchars($game['name']); ?>">
                            <p class="game-name-overlay"><?php echo htmlspecialchars($game['name']); ?></p>
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="card-actions">
                                <?php if (in_array($game['id'], $user_wishlist_game_ids)): ?>
                                    <button class="btn btn-danger btn-small remove-from-wishlist" data-game-id="<?php echo $game['id']; ?>">
                                        <i class="fas fa-heart-broken"></i> Hapus Wishlist
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-primary btn-small add-to-wishlist" data-game-id="<?php echo $game['id']; ?>">
                                        <i class="fas fa-heart"></i> Tambah Wishlist
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="features">
            <h1>Keunggulan Top Up Kami</h1>
            <div class="feature-row">
                <div class="feature">
                    <div class="feature-icon-ml"></div>
                    <div class="feature-content">
                        <h3>Mobile Legends</h3>
                        <p>Game MOBA (Multiplayer Online Battle Arena) yang populer di Indonesia dan di seluruh dunia.</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon-PUBG"></div>
                    <div class="feature-content">
                        <h3>PUBG Mobile</h3>
                        <p>Game battle royale online multipemain di mana hingga 100 pemain bertempur dalam pertandingan last man standing.</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon-ff"></div>
                    <div class="feature-content">
                        <h3>Free Fire</h3>
                        <p>Game battle royale seru dan penuh aksi, di mana 50 pemain bertarung di satu pulau hingga hanya satu yang bertahan.</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon-genshin"></div>
                    <div class="feature-content">
                        <h3>Genshin Impact</h3>
                        <p>Game aksi role-playing (RPG) dunia terbuka yang dikembangkan oleh HoYoverse.</p>
                    </div>
                </div>
            </div>
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
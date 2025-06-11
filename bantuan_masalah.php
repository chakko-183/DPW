<?php
session_start();
require_once 'config/database.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?message=Anda harus login untuk melihat notifikasi.&type=danger');
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


// Handle mark as read action via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_as_read') {
    header('Content-Type: application/json');
    $notification_id = filter_var($_POST['notification_id'], FILTER_VALIDATE_INT);

    if ($notification_id) {
        $db->query('UPDATE notifications SET is_read = TRUE WHERE id = :id AND (user_id = :user_id OR user_id IS NULL)');
        $db->bind(':id', $notification_id);
        $db->bind(':user_id', $_SESSION['user_id']);

        if ($db->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark as read.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID.']);
    }
    exit();
}

// Fetch notifications for the current user (general and specific)
$notifications = [];
try {
    $db->query('SELECT * FROM notifications WHERE user_id = :user_id OR user_id IS NULL ORDER BY created_at DESC');
    $db->bind(':user_id', $_SESSION['user_id']);
    $notifications = $db->resultset();
} catch (PDOException $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat notifikasi.";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Anda</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Bantuan & Masalah</h1>
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

    <!-- <main class="fade-in"> -->
    <section class="bantuan-container">
        <h2>🛠️ Bantuan & Masalah</h2>
        <form action="index.html" method="get" class="form-bantuan">
            <label for="nama">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" required placeholder="Masukkan nama Anda">

            <label for="email">Alamat Email:</label>
            <input type="email" id="email" name="email" required placeholder="nama@email.com">

            <label for="masalah">Jenis Masalah:</label>
            <select id="masalah" name="masalah" required>
                <option value="">-- Pilih Masalah --</option>
                <option value="login">Tidak bisa login</option>
                <option value="error">Error pada tampilan</option>
                <option value="fitur">Permintaan fitur</option>
                <option value="lainnya">Lainnya</option>
            </select>

            <label for="pesan">Pesan / Penjelasan:</label>
            <textarea id="pesan" name="pesan" rows="5" required placeholder="Jelaskan masalah yang Anda alami..."></textarea>
            <button type="submit">Kirim</button>
        </form>
    </section>
    <!-- </main> -->

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
            <a href="faq.php" class="footer-link">Halaman FAQ</a>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.notification-mark-read-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const notificationItem = this.closest('.notification-item');
                    const notificationId = notificationItem.dataset.notificationId;

                    fetch('notifications.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=mark_as_read&notification_id=' + notificationId
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                notificationItem.classList.remove('unread');
                                notificationItem.classList.add('read');
                                this.remove();
                                const badge = document.querySelector('.notification-badge');
                                if (badge) {
                                    let count = parseInt(badge.textContent);
                                    if (count > 0) { // Ensure count is positive before decrementing
                                        badge.textContent = count - 1;
                                        if (count - 1 === 0) {
                                            badge.remove(); // Remove badge if count becomes 0
                                        }
                                    }
                                }
                            } else {
                                alert('Gagal menandai notifikasi sudah dibaca: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Error marking as read:', error));
                });
            });
        });
    </script>
</body>

</html>
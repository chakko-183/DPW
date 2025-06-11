<?php
session_start();
require_once 'config/database.php';

$message = '';
$message_type = '';
$token_valid = false;
$token = $_GET['token'] ?? '';

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


if (empty($token)) {
    $message = "Tautan reset tidak valid.";
    $message_type = "danger";
} else {
    $db->query('SELECT id, email, reset_token_expiry FROM users WHERE reset_token = :token');
    $db->bind(':token', $token);
    $user = $db->single();

    if ($user) {
        $current_time = new DateTime();
        $expiry_time = new DateTime($user['reset_token_expiry']);

        if ($current_time < $expiry_time) {
            $token_valid = true;
        } else {
            $message = "Tautan reset telah kadaluarsa. Silakan minta tautan baru.";
            $message_type = "danger";
        }
    } else {
        $message = "Tautan reset tidak valid atau sudah digunakan.";
        $message_type = "danger";
    }
}

if ($token_valid && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || empty($confirm_password)) {
        $message = "Password baru dan konfirmasi password harus diisi.";
        $message_type = "danger";
    } elseif ($new_password !== $confirm_password) {
        $message = "Konfirmasi password tidak cocok.";
        $message_type = "danger";
    } elseif (strlen($new_password) < 6) {
        $message = "Password minimal 6 karakter.";
        $message_type = "danger";
    } else {
        $hashed_password = $new_password; // Hashing is strongly recommended for production: password_hash($new_password, PASSWORD_DEFAULT);

        $db->query('UPDATE users SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id');
        $db->bind(':password', $hashed_password);
        $db->bind(':id', $user['id']);

        if ($db->execute()) {
            $message = "Password Anda berhasil diatur ulang! Silakan login.";
            $message_type = "success";
            $token_valid = false;
            header('Refresh: 3; URL=index.php');
            exit();
        } else {
            $message = "Terjadi kesalahan saat mengatur ulang password. Silakan coba lagi.";
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Reset Password</h1>
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
        <section class="login-section">
            <div class="form-container">
                <div class="right-container">
                    <div class="right-inner-container">
                        <h2>Atur Ulang Password Baru</h2>

                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($token_valid): ?>
                            <form method="POST">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                                <div class="input-field">
                                    <input type="password" id="password" name="password" required autocomplete="new-password" placeholder=" ">
                                    <label for="password">Password Baru</label>
                                    <i class="fas fa-lock"></i>
                                    <i class="fas fa-eye-slash toggle-password"></i>
                                </div>

                                <div class="input-field">
                                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" placeholder=" ">
                                    <label for="confirm_password">Konfirmasi Password Baru</label>
                                    <i class="fas fa-lock"></i>
                                </div>

                                <button type="submit" class="btn-primary login-btn">
                                    <span>Reset Password</span>
                                    <i class="fas fa-redo"></i>
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="register-link">
                            <p><a href="index.php">Kembali ke Login</a></p>
                        </div>
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
<?php
session_start();
require_once 'config/database.php';

$message = '';
$message_type = '';

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
    $email = $_POST['email'];

    $db->query('SELECT id FROM users WHERE email = :email');
    $db->bind(':email', $email);
    $user = $db->single();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db->query('UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id');
        $db->bind(':token', $token);
        $db->bind(':expiry', $expiry);
        $db->bind(':id', $user['id']);

        if ($db->execute()) {
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
            $subject = "Reset Password Akun Top Up Game Anda";
            $body = "Halo,\n\n"
                . "Anda telah meminta reset password. Silakan klik tautan berikut untuk mengatur ulang password Anda:\n"
                . $reset_link . "\n\n"
                . "Tautan ini akan kadaluarsa dalam 1 jam.\n"
                . "Jika Anda tidak meminta ini, abaikan email ini.\n\n"
                . "Terima kasih,\nTim Top Up Game";

            // mail($email, $subject, $body, "From: no-reply@yourdomain.com");

            $message = "Tautan reset password telah dikirim ke email Anda. Silakan cek kotak masuk Anda.";
            $message_type = "success";
            $message .= "<br><small>Link Reset (hanya untuk demo): <a href='" . $reset_link . "'>" . $reset_link . "</a></small>";
        } else {
            $message = "Terjadi kesalahan saat membuat tautan reset. Silakan coba lagi.";
            $message_type = "danger";
        }
    } else {
        $message = "Email tidak terdaftar.";
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lupa Password</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Lupa Password</h1>
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
                        <h2>Reset Password Anda</h2>

                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="input-field">
                                <input type="email" id="email" name="email" required autocomplete="off" placeholder=" ">
                                <label for="email">Masukkan Email Anda</label>
                                <i class="fas fa-envelope"></i>
                            </div>

                            <button type="submit" class="btn-primary login-btn">
                                <span>Kirim Tautan Reset</span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>

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
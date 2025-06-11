<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

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
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $db->query('SELECT * FROM users WHERE email = :email');
        $db->bind(':email', $email);
        $existing_user = $db->single();

        if ($existing_user) {
            $error = "Email ini sudah terdaftar. Silakan gunakan email lain atau login.";
        } else {
            $hashed_password = $password; // Hashing is strongly recommended for production: password_hash($password, PASSWORD_DEFAULT);

            $db->query('INSERT INTO users (email, password, role) VALUES (:email, :password, :role)');
            $db->bind(':email', $email);
            $db->bind(':password', $hashed_password);
            $db->bind(':role', 'user');

            if ($db->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
                header('Location: index.php?registered=1');
                exit();
            } else {
                $error = "Terjadi kesalahan saat registrasi. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Akun Baru</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Daftar Akun Baru</h1>
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
                        <h2>Buat Akun Baru</h2>

                        <?php if (isset($success) && $success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?> <a href="index.php" class="alert-link">Login Sekarang</a>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($error) && $error): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="input-field">
                                <input type="email" id="email" name="email" required autocomplete="off" placeholder=" ">
                                <label for="email">Email Address</label>
                                <i class="fas fa-envelope"></i>
                            </div>

                            <div class="input-field">
                                <input type="password" id="password" name="password" required autocomplete="off" placeholder=" ">
                                <label for="password">Password</label>
                                <i class="fas fa-lock"></i>
                                <i class="fas fa-eye-slash toggle-password"></i>
                            </div>

                            <div class="input-field">
                                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="off" placeholder=" ">
                                <label for="confirm_password">Konfirmasi Password</label>
                                <i class="fas fa-lock"></i>
                            </div>

                            <button type="submit" class="btn-primary login-btn">
                                <span>Daftar Akun</span>
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </form>

                        <div class="register-link">
                            <p>Sudah punya akun? <a href="index.php">Login Sekarang</a></p>
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
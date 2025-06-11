<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query untuk mencari user berdasarkan email
    $db->query('SELECT * FROM users WHERE email = :email');
    $db->bind(':email', $email);
    $user = $db->single();

    // Perbandingan password biasa (karena password di database tidak di-hash)
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Arahkan berdasarkan role
        if ($user['role'] === 'admin') {
            header('Location: admin/index.php');
        } elseif ($user['role'] === 'user') {
            header('Location: daftar-game.php');
        }
        exit();
    } else {
        $error = "Email atau password salah!";
    }
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Top Up Game - Beranda</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <header>
        <h1 class="fade-in">Top Up Game</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="riwayat.php">Riwayat</a>
        </nav>
    </header>

    <div class="container">
        <div class="form-container">
            <div class="right-container">
                <div class="right-inner-container">
                    <h2>Login to Account</h2>

                    <?php if (isset($error)): ?>
                        <div style="color: red; text-align: center; margin-bottom: 20px;">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="input-group">
                            <div class="input-field">
                                <input type="email" id="email" name="email" required autocomplete="off">
                                <label for="email">Email Address</label>
                                <i class="fas fa-envelope"></i>
                            </div>

                            <div class="input-field">
                                <input type="password" id="password" name="password" required autocomplete="off">
                                <label for="password">Password</label>
                                <i class="fas fa-lock"></i>
                                <i class="fas fa-eye-slash toggle-password"></i>
                            </div>

                            <div class="options-container">
                                <div class="remember-me">
                                    <label for="remember">Remember me</label>
                                </div>
                                <a href="#" class="forgot-password">Forgot Password?</a>
                            </div>
                        </div>

                        <button type="submit" class="login-btn">
                            <span>Login Now</span>
                            <a href="daftar-game.php"></a>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="register-link">
                        <p>Don't have an account? <a href="#">Register Now</a></p>
                        <p style="margin-top: 10px; font-size: 12px; color: #666;">
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>&copy; 2025 Top Up Game. All rights reserved.</footer>
    <script src="script.js"></script>
</body>

</html>
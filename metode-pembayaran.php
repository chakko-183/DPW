<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['topup_data'])) {
    header('Location: daftar-game.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['topup_data']['payment_method'] = $_POST['metode'];
    header('Location: konfirmasi.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Metode Pembayaran</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <header>
        <h1 class="fade-in">Metode Pembayaran</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="form-topup.php">Form Top Up</a>
        </nav>
    </header>

    <div class="loading-container">Loading</div>

    <main>
        <form class="payment-form" method="POST">
            <label for="metode">Pilih Metode Pembayaran:</label>
            <select id="metode" name="metode" required>
                <option value="GoPay">GoPay</option>
                <option value="DANA">DANA</option>
                <option value="OVO">OVO</option>
                <option value="Transfer Bank">Transfer Bank</option>
            </select>

            <button type="submit" class="btn-topup">Konfirmasi & Checkout</button>
        </form>
    </main>

    <footer>&copy; 2025 Top Up Game. All rights reserved.</footer>
    <script src="script.js"></script>
</body>

</html>
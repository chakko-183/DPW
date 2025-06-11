<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['topup_data'])) {
    header('Location: daftar-game.php');
    exit();
}

$data = $_SESSION['topup_data'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insert transaction
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
</head>

<body>
    <header>
        <h1>Konfirmasi & Checkout</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="daftar-game.php">Daftar Game</a>
        </nav>
    </header>

    <div class="loading-container">Loading</div>

    <main class="fade-in">
        <h2>Ringkasan Pemesanan</h2>
        <div class="summary">
            <p><strong>Game:</strong> <?php echo $data['game_name']; ?></p>
            <p><strong>ID Pengguna:</strong> <?php echo $data['userid']; ?></p>
            <p><strong>Server:</strong> <?php echo $data['server']; ?></p>
            <p><strong>Jumlah Top Up:</strong> <?php echo $data['diamond_amount']; ?> Diamonds</p>
            <p><strong>Harga:</strong> Rp <?php echo number_format($data['price'], 0, ',', '.'); ?></p>
            <p><strong>Metode Pembayaran:</strong> <?php echo $data['payment_method']; ?></p>
        </div>

        <form method="POST">
            <button type="submit" class="btn-topup">Selesaikan & Bayar</button>
        </form>
    </main>

    <footer>&copy; 2025 Top Up Game. All rights reserved.</footer>
    <script src="script.js"></script>
</body>

</html>
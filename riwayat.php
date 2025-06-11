<?php
session_start();
require_once 'config/database.php';

// Get transactions with game names
$db->query('SELECT t.*, g.name as game_name 
            FROM transactions t 
            LEFT JOIN games g ON t.game_id = g.id 
            ORDER BY t.created_at DESC 
            LIMIT 20');
$transactions = $db->resultset();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Riwayat Transaksi</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <header>
        <h1 class="fade-in">Report Riwayat Transaksi</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="daftar-game.php">Daftar Game</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin/index.php">Admin Panel</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="loading-container">Loading</div>

    <main>
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #4CAF50; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                Transaksi berhasil diproses!
            </div>
        <?php endif; ?>

        <ul class="history-list">
            <?php foreach ($transactions as $transaction): ?>
                <li>
                    <?php echo $transaction['game_name']; ?> -
                    <?php echo $transaction['diamond_amount']; ?> Diamonds -
                    <span style="color: <?php echo $transaction['status'] == 'success' ? '#4CAF50' : ($transaction['status'] == 'failed' ? '#f44336' : '#ff9800'); ?>">
                        <?php echo ucfirst($transaction['status']); ?>
                    </span> -
                    Rp <?php echo number_format($transaction['total_price'], 0, ',', '.'); ?> -
                    <?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>

    <footer>&copy; 2025 Top Up Game. All rights reserved.</footer>
    <script src="script.js"></script>
</body>

</html>
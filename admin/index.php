<?php
session_start();
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get statistics
$db->query('SELECT COUNT(*) as total FROM games WHERE status = "active"');
$total_games = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM transactions');
$total_transactions = $db->single()['total'];

$db->query('SELECT SUM(total_price) as total FROM transactions WHERE status = "success"');
$total_revenue = $db->single()['total'] ?? 0;

$db->query('SELECT COUNT(*) as total FROM transactions WHERE status = "success"');
$success_transactions = $db->single()['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #555555;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #ff4b2b;
        }

        .admin-nav {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .admin-nav a {
            background: #ff4b2b;
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .admin-nav a:hover {
            background: #ff3b1b;
        }
    </style>
</head>

<body>
    <header>
        <h1 class="fade-in">Admin Dashboard</h1>
        <nav>
            <a href="../index.php">Beranda</a>
            <a href="../daftar-game.php">Daftar Game</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="admin-container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_games; ?></div>
                <div>Total Games</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_transactions; ?></div>
                <div>Total Transaksi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $success_transactions; ?></div>
                <div>Transaksi Sukses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
                <div>Total Revenue</div>
            </div>
        </div>

        <div class="admin-nav">
            <a href="games.php">Kelola Games</a>
            <a href="transactions.php">Kelola Transaksi</a>
            <a href="packages.php">Kelola Paket Diamond</a>
        </div>

        <div style="background: #555555; padding: 20px; border-radius: 10px;">
            <h3>Selamat datang, <?php echo $_SESSION['email']; ?>!</h3>
            <p>Anda login sebagai Administrator. Gunakan menu di atas untuk mengelola website.</p>
        </div>
    </div>

    <footer>&copy; 2025 Top Up Game. All rights reserved.</footer>
</body>

</html>
<?php
session_start();
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $db->query('UPDATE transactions SET status = :status WHERE id = :id');
    $db->bind(':status', $_POST['status']);
    $db->bind(':id', $_POST['transaction_id']);
    $db->execute();
    $success = "Status transaksi berhasil diupdate!";
}

// Get all transactions with game names
$db->query('SELECT t.*, g.name as game_name 
            FROM transactions t 
            LEFT JOIN games g ON t.game_id = g.id 
            ORDER BY t.created_at DESC');
$transactions = $db->resultset();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Transaksi</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .transactions-table {
            background: #555555;
            border-radius: 10px;
            overflow-x: auto;
        }

        .transactions-table table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .transactions-table th,
        .transactions-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #666;
            font-size: 14px;
        }

        .transactions-table th {
            background: #333;
            position: sticky;
            top: 0;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-success {
            background: #4CAF50;
            color: white;
        }

        .status-pending {
            background: #ff9800;
            color: white;
        }

        .status-failed {
            background: #f44336;
            color: white;
        }

        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-primary {
            background: #ff4b2b;
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            background: #4CAF50;
            color: white;
        }
    </style>
</head>

<body>
    <header>
        <h1 class="fade-in">Kelola Transaksi</h1>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="games.php">Games</a>
            <a href="../index.php">Beranda</a>
        </nav>
    </header>

    <div class="admin-container">
        <?php if (isset($success)): ?>
            <div class="alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="transactions-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Game</th>
                        <th>Server</th>
                        <th>Diamonds</th>
                        <th>Harga</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo $transaction['id']; ?></td>
                            <td><?php echo $transaction['user_id']; ?></td>
                            <td><?php echo $transaction['game_name']; ?></td>
                            <td><?php echo $transaction['server']; ?></td>
                            <td><?php echo $transaction['diamond_amount']; ?></td>
                            <td>Rp <?php echo number_format($transaction['total_price'], 0, ',', '.'); ?></td>
                            <td><?php echo $transaction['payment_method']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                    <?php echo ucfirst($transaction['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $transaction['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="success" <?php echo $transaction['status'] == 'success' ? 'selected' : ''; ?>>Success</option>
                                        <option value="failed" <?php echo $transaction['status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>&copy; 2025 Top Up Game. All rights reserved.</footer>
</body>

</html>
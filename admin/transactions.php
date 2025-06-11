<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $db->query('UPDATE transactions SET status = :status WHERE id = :id');
    $db->bind(':status', $_POST['status']);
    $db->bind(':id', $_POST['transaction_id']);
    if ($db->execute()) {
        $success = "Status transaksi berhasil diupdate!";
    } else {
        $error = "Gagal memperbarui status transaksi.";
    }
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Kelola Transaksi</h1>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="games.php">Games</a>
            <!-- <a href="transactions.php">Transaksi</a> -->
            <a href="packages.php">Paket Diamond</a>
            <a href="reviews.php">Ulasan</a>
            <a href="notifications.php">Notifikasi</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="admin-container fade-in">
        <section class="transaction-management">
            <h2>Daftar Transaksi</h2>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
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
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center;">Tidak ada transaksi ditemukan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo $transaction['id']; ?></td>
                                    <td><?php echo htmlspecialchars($transaction['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['game_name']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['server']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['diamond_amount']); ?></td>
                                    <td>Rp <?php echo number_format($transaction['total_price'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['payment_method']); ?></td>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- <footer>
        <div class="footer-section">
            <h4>Tentang Kami</h4>
            <div class="footer-content">
                <p>© 2025 Top Up Game. All rights reserved.</p>
                <p>Website ini dibuat untuk memenuhi tugas akhir mata kuliah Dasar Pemrograman Web.</p>
            </div>
        </div>

        <div class="footer-section">
            <h4>Bantuan & Informasi</h4>
            <a href="#" class="footer-link">🛠 Bantuan & Masalah</a>
            <a href="#" class="footer-link">Halaman FAQ</a>
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
    </footer> -->
    <footer class="footer">
        <div class="footer-left">
            <p>© 2025 Kelompok 1 - Teknik Informatika</p>
        </div>
    </footer>
    <script src="../script.js"></script>
</body>

</html>
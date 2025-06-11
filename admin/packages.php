<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $db->query('INSERT INTO diamond_packages (game_id, amount, price, status) VALUES (:game_id, :amount, :price, :status)');
                $db->bind(':game_id', $_POST['game_id']);
                $db->bind(':amount', $_POST['amount']);
                $db->bind(':price', $_POST['price']);
                $db->bind(':status', $_POST['status']);
                if ($db->execute()) {
                    $success = "Paket diamond berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan paket diamond.";
                }
                break;

            case 'edit':
                $db->query('UPDATE diamond_packages SET game_id = :game_id, amount = :amount, price = :price, status = :status WHERE id = :id');
                $db->bind(':game_id', $_POST['game_id']);
                $db->bind(':amount', $_POST['amount']);
                $db->bind(':price', $_POST['price']);
                $db->bind(':status', $_POST['status']);
                $db->bind(':id', $_POST['id']);
                if ($db->execute()) {
                    $success = "Paket diamond berhasil diupdate!";
                } else {
                    $error = "Gagal mengupdate paket diamond.";
                }
                break;

            case 'delete':
                $db->query('DELETE FROM diamond_packages WHERE id = :id');
                $db->bind(':id', $_POST['id']);
                if ($db->execute()) {
                    $success = "Paket diamond berhasil dihapus permanen!";
                } else {
                    $error = "Gagal menghapus paket diamond.";
                }
                break;
        }
    }
}

$db->query('SELECT dp.*, g.name as game_name
            FROM diamond_packages dp
            LEFT JOIN games g ON dp.game_id = g.id
            ORDER BY g.name, dp.amount ASC');
$packages = $db->resultset();

$db->query('SELECT id, name FROM games WHERE status = "active" ORDER BY name');
$games_list = $db->resultset();

$edit_package = null;
if (isset($_GET['edit'])) {
    $db->query('SELECT * FROM diamond_packages WHERE id = :id');
    $db->bind(':id', $_GET['edit']);
    $edit_package = $db->single();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Paket Diamond</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <header>
        <h1 class="fade-in">Kelola Paket Diamond</h1>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="games.php">Games</a>
            <a href="transactions.php">Transaksi</a>
            <!-- <a href="packages.php">Paket Diamond</a> -->
            <a href="reviews.php">Ulasan</a>
            <a href="notifications.php">Notifikasi</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="loading-container">Loading</div>

    <main class="admin-container fade-in">
        <section class="package-form-section">
            <h2><?php echo $edit_package ? 'Edit Paket Diamond' : 'Tambah Paket Diamond Baru'; ?></h2>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="form-section">
                <input type="hidden" name="action" value="<?php echo $edit_package ? 'edit' : 'add'; ?>">
                <?php if ($edit_package): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_package['id']; ?>">
                <?php endif; ?>

                <div class="input-group">
                    <label for="game_id">Pilih Game:</label>
                    <select id="game_id" name="game_id" required>
                        <?php foreach ($games_list as $game_item): ?>
                            <option value="<?php echo $game_item['id']; ?>"
                                <?php echo ($edit_package && $edit_package['game_id'] == $game_item['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($game_item['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label for="amount">Jumlah Diamond:</label>
                    <input type="number" id="amount" name="amount" placeholder="Jumlah Diamond"
                        value="<?php echo $edit_package ? htmlspecialchars($edit_package['amount']) : ''; ?>" required>
                </div>

                <div class="input-group">
                    <label for="price">Harga (Rp):</label>
                    <input type="number" step="0.01" id="price" name="price" placeholder="Harga"
                        value="<?php echo $edit_package ? htmlspecialchars($edit_package['price']) : ''; ?>" required>
                </div>

                <div class="input-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="active" <?php echo ($edit_package && $edit_package['status'] == 'active') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo ($edit_package && $edit_package['status'] == 'inactive') ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_package ? 'Update Paket' : 'Tambah Paket'; ?>
                    </button>

                    <?php if ($edit_package): ?>
                        <a href="packages.php" class="btn btn-warning">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="package-list-section">
            <h2>Daftar Paket Diamond</h2>
            <div class="packages-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game</th>
                            <th>Jumlah Diamond</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($packages)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Tidak ada paket diamond ditemukan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($packages as $package): ?>
                                <tr>
                                    <td><?php echo $package['id']; ?></td>
                                    <td><?php echo htmlspecialchars($package['game_name']); ?></td>
                                    <td><?php echo htmlspecialchars($package['amount']); ?></td>
                                    <td>Rp <?php echo number_format($package['price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $package['status']; ?>">
                                            <?php echo ucfirst($package['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="packages.php?edit=<?php echo $package['id']; ?>" class="btn btn-warning btn-small"><i class="fas fa-edit"></i> Edit</a>
                                        <form method="POST" style="display: inline-block; margin-left: 5px;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $package['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small"
                                                onclick="return confirm('Yakin ingin menghapus paket diamond ini secara permanen?')"><i class="fas fa-trash-alt"></i> Hapus</button>
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

    <footer class="footer">
        <div class="footer-left">
            <p>© 2025 Kelompok 1 - Teknik Informatika</p>
        </div>
    </footer>
    <script src="../script.js"></script>
</body>

</html>
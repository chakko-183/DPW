<?php
session_start();
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$message = '';
$message_type = '';

// Handle review status update or deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $db->query('UPDATE reviews SET status = :status WHERE id = :id');
        $db->bind(':status', $_POST['status']);
        $db->bind(':id', $_POST['review_id']);
        if ($db->execute()) {
            $message = "Status ulasan berhasil diupdate!";
            $message_type = "success";
        } else {
            $message = "Gagal memperbarui status ulasan.";
            $message_type = "danger";
        }
    } elseif ($_POST['action'] === 'delete_review') {
        $db->query('DELETE FROM reviews WHERE id = :id');
        $db->bind(':id', $_POST['review_id']);
        if ($db->execute()) {
            $message = "Ulasan berhasil dihapus permanen!";
            $message_type = "success";
        } else {
            $message = "Gagal menghapus ulasan.";
            $message_type = "danger";
        }
    }
}

// Fetch all reviews
$db->query('SELECT * FROM reviews ORDER BY created_at DESC');
$reviews = $db->resultset();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Ulasan</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Kelola Ulasan Pengguna</h1>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="games.php">Games</a>
            <a href="transactions.php">Transaksi</a>
            <a href="packages.php">Paket Diamond</a>
            <!-- <a href="reviews.php">Ulasan</a> -->
            <a href="notifications.php">Notifikasi</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="admin-container fade-in">
        <section class="review-management">
            <h2>Daftar Ulasan</h2>
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="reviews-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Ulasan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Tidak ada ulasan ditemukan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?php echo $review['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($review['username']); ?>
                                        <?php if ($review['user_id']): ?>
                                            <br><small>(ID: <?php echo $review['user_id']; ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo str_repeat('&#9733;', $review['rating']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $review['status']; ?>">
                                            <?php echo ucfirst($review['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $review['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="approved" <?php echo $review['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="rejected" <?php echo $review['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                            <input type="hidden" name="action" value="update_status">
                                        </form>
                                        <form method="POST" style="display: inline-block; margin-left: 5px;">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                            <input type="hidden" name="action" value="delete_review">
                                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Yakin ingin menghapus ulasan ini secara permanen?')"><i class="fas fa-trash-alt"></i> Hapus</button>
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
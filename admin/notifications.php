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

// Handle form submissions (add/edit/delete notification)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        // Mengganti FILTER_SANITIZE_STRING dengan filter_input(FILTER_UNSAFE_RAW) dan htmlspecialchars
        $message_text = htmlspecialchars(filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW), ENT_QUOTES, 'UTF-8');
        $is_read = isset($_POST['is_read']) ? 1 : 0;

        // Validate message text
        if (empty($message_text)) {
            $message = "Pesan notifikasi tidak boleh kosong.";
            $message_type = "danger";
        } else {
            // Check if user_id exists if provided
            if ($user_id) {
                $db->query('SELECT id FROM users WHERE id = :user_id');
                $db->bind(':user_id', $user_id);
                if (!$db->single()) {
                    $message = "User ID tidak ditemukan. Notifikasi akan dibuat sebagai notifikasi umum.";
                    $user_id = NULL; // Fallback to general notification
                }
            } else {
                $user_id = NULL; // Ensure it's NULL if not provided or invalid
            }

            $db->query('INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (:user_id, :message, :is_read, NOW())');
            $db->bind(':user_id', $user_id);
            $db->bind(':message', $message_text);
            $db->bind(':is_read', $is_read);

            if ($db->execute()) {
                $message = "Notifikasi berhasil ditambahkan!";
                $message_type = "success";
            } else {
                $message = "Gagal menambahkan notifikasi.";
                $message_type = "danger";
            }
        }
    } elseif ($_POST['action'] === 'edit') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        // Mengganti FILTER_SANITIZE_STRING dengan filter_input(FILTER_UNSAFE_RAW) dan htmlspecialchars
        $message_text = htmlspecialchars(filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW), ENT_QUOTES, 'UTF-8');
        $is_read = isset($_POST['is_read']) ? 1 : 0;

        if (!$id || empty($message_text)) {
            $message = "ID notifikasi atau pesan tidak valid.";
            $message_type = "danger";
        } else {
            if ($user_id) {
                $db->query('SELECT id FROM users WHERE id = :user_id');
                $db->bind(':user_id', $user_id);
                if (!$db->single()) {
                    $message = "User ID tidak ditemukan. Notifikasi akan disimpan sebagai notifikasi umum.";
                    $user_id = NULL;
                }
            } else {
                $user_id = NULL;
            }

            $db->query('UPDATE notifications SET user_id = :user_id, message = :message, is_read = :is_read WHERE id = :id');
            $db->bind(':user_id', $user_id);
            $db->bind(':message', $message_text);
            $db->bind(':is_read', $is_read);
            $db->bind(':id', $id);

            if ($db->execute()) {
                $message = "Notifikasi berhasil diupdate!";
                $message_type = "success";
            } else {
                $message = "Gagal mengupdate notifikasi.";
                $message_type = "danger";
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $db->query('DELETE FROM notifications WHERE id = :id');
            $db->bind(':id', $id);
            if ($db->execute()) {
                $message = "Notifikasi berhasil dihapus!";
                $message_type = "success";
            } else {
                $message = "Gagal menghapus notifikasi.";
                $message_type = "danger";
            }
        } else {
            $message = "ID notifikasi tidak valid.";
            $message_type = "danger";
        }
    }
}

// Fetch all notifications
$db->query('SELECT n.*, u.email as user_email FROM notifications n LEFT JOIN users u ON n.user_id = u.id ORDER BY n.created_at DESC');
$notifications = $db->resultset();

// Get notification for editing
$edit_notification = null;
if (isset($_GET['edit'])) {
    $id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($id) {
        $db->query('SELECT * FROM notifications WHERE id = :id');
        $db->bind(':id', $id);
        $edit_notification = $db->single();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Notifikasi</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Kelola Notifikasi</h1>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="games.php">Games</a>
            <a href="transactions.php">Transaksi</a>
            <a href="packages.php">Paket Diamond</a>
            <a href="reviews.php">Ulasan</a>
            <!-- <a href="notifications.php">Notifikasi</a> -->
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="admin-container fade-in">
        <section class="notification-form-section">
            <h2><?php echo $edit_notification ? 'Edit Notifikasi' : 'Buat Notifikasi Baru'; ?></h2>
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-section">
                <input type="hidden" name="action" value="<?php echo $edit_notification ? 'edit' : 'add'; ?>">
                <?php if ($edit_notification): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_notification['id']; ?>">
                <?php endif; ?>

                <div class="input-group">
                    <label for="message">Pesan Notifikasi:</label>
                    <textarea id="message" name="message" placeholder="Tulis pesan notifikasi..." required><?php echo $edit_notification ? htmlspecialchars($edit_notification['message']) : ''; ?></textarea>
                </div>

                <div class="input-group">
                    <label for="user_id">Target User ID (opsional, kosongkan untuk semua user):</label>
                    <input type="number" id="user_id" name="user_id" placeholder="Misal: 123"
                        value="<?php echo $edit_notification && $edit_notification['user_id'] ? htmlspecialchars($edit_notification['user_id']) : ''; ?>">
                </div>

                <div class="input-group checkbox-group" style="text-align: left;">
                    <input type="checkbox" id="is_read" name="is_read" value="1" <?php echo ($edit_notification && $edit_notification['is_read']) ? 'checked' : ''; ?>>
                    <label for="is_read" style="display: inline-block; margin-left: 10px;">Sudah Dibaca?</label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_notification ? 'Update Notifikasi' : 'Tambah Notifikasi'; ?>
                    </button>
                    <?php if ($edit_notification): ?>
                        <a href="notifications.php" class="btn btn-warning">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="notification-list-section">
            <h2>Daftar Notifikasi</h2>
            <div class="notifications-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Untuk User ID</th>
                            <th>Pesan</th>
                            <th>Sudah Dibaca</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notifications)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Tidak ada notifikasi ditemukan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <tr>
                                    <td><?php echo $notification['id']; ?></td>
                                    <td><?php echo $notification['user_id'] ? htmlspecialchars($notification['user_id']) . ' (' . htmlspecialchars($notification['user_email']) . ')' : 'Semua User'; ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($notification['message'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $notification['is_read'] ? 'success' : 'pending'; ?>">
                                            <?php echo $notification['is_read'] ? 'Ya' : 'Belum'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></td>
                                    <td>
                                        <a href="notifications.php?edit=<?php echo $notification['id']; ?>" class="btn btn-warning btn-small"><i class="fas fa-edit"></i> Edit</a>
                                        <form method="POST" style="display: inline-block; margin-left: 5px;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $notification['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Yakin ingin menghapus notifikasi ini?')"><i class="fas fa-trash-alt"></i> Hapus</button>
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
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
                $db->query('INSERT INTO games (name, image, description) VALUES (:name, :image, :description)');
                $db->bind(':name', $_POST['name']);
                $db->bind(':image', $_POST['image']);
                $db->bind(':description', $_POST['description']);
                if ($db->execute()) {
                    $success = "Game berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan game.";
                }
                break;

            case 'edit':
                $db->query('UPDATE games SET name = :name, image = :image, description = :description WHERE id = :id');
                $db->bind(':name', $_POST['name']);
                $db->bind(':image', $_POST['image']);
                $db->bind(':description', $_POST['description']);
                $db->bind(':id', $_POST['id']);
                if ($db->execute()) {
                    $success = "Game berhasil diupdate!";
                } else {
                    $error = "Gagal mengupdate game.";
                }
                break;

            case 'delete':
                $db->query('UPDATE games SET status = "inactive" WHERE id = :id');
                $db->bind(':id', $_POST['id']);
                if ($db->execute()) {
                    $success = "Game berhasil dinonaktifkan!";
                } else {
                    $error = "Gagal menonaktifkan game.";
                }
                break;
        }
    }
}

$db->query('SELECT * FROM games ORDER BY created_at DESC');
$games = $db->resultset();

$edit_game = null;
if (isset($_GET['edit'])) {
    $db->query('SELECT * FROM games WHERE id = :id');
    $db->bind(':id', $_GET['edit']);
    $edit_game = $db->single();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Games</title>
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Kelola Games</h1>
        <nav>
            <a href="index.php">Dashboard</a>
            <!-- <a href="games.php">Games</a> -->
            <a href="transactions.php">Transaksi</a>
            <a href="packages.php">Paket Diamond</a>
            <a href="reviews.php">Ulasan</a>
            <a href="notifications.php">Notifikasi</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="admin-container fade-in">
        <section class="game-form-section">
            <h2><?php echo $edit_game ? 'Edit Game' : 'Tambah Game Baru'; ?></h2>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="form-section">
                <input type="hidden" name="action" value="<?php echo $edit_game ? 'edit' : 'add'; ?>">
                <?php if ($edit_game): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_game['id']; ?>">
                <?php endif; ?>

                <div class="input-group">
                    <label for="name">Nama Game:</label>
                    <input type="text" id="name" name="name" placeholder="Nama Game"
                        value="<?php echo $edit_game ? htmlspecialchars($edit_game['name']) : ''; ?>" required>
                </div>

                <div class="input-group">
                    <label for="image">Path Gambar:</label>
                    <input type="text" id="image" name="image" placeholder="Contoh: gambar/game.jpg"
                        value="<?php echo $edit_game ? htmlspecialchars($edit_game['image']) : ''; ?>" required>
                </div>

                <div class="input-group">
                    <label for="description">Deskripsi Game:</label>
                    <textarea id="description" name="description" placeholder="Deskripsi Game" required><?php echo $edit_game ? htmlspecialchars($edit_game['description']) : ''; ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_game ? 'Update Game' : 'Tambah Game'; ?>
                    </button>

                    <?php if ($edit_game): ?>
                        <a href="games.php" class="btn btn-warning">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="game-list-section">
            <h2>Daftar Game Terdaftar</h2>
            <div class="games-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($games)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Belum ada game yang terdaftar.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($games as $game): ?>
                                <tr>
                                    <td><?php echo $game['id']; ?></td>
                                    <td><img src="../<?php echo htmlspecialchars($game['image']); ?>" alt="<?php echo htmlspecialchars($game['name']); ?>"></td>
                                    <td><?php echo htmlspecialchars($game['name']); ?></td>
                                    <td><?php echo substr(htmlspecialchars($game['description']), 0, 70) . '...'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $game['status']; ?>">
                                            <?php echo ucfirst($game['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="games.php?edit=<?php echo $game['id']; ?>" class="btn btn-warning btn-small"><i class="fas fa-edit"></i> Edit</a>
                                        <?php if ($game['status'] == 'active'): ?>
                                            <form method="POST" style="display: inline-block; margin-left: 5px;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $game['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-small"
                                                    onclick="return confirm('Yakin ingin menonaktifkan game ini? Ini juga akan menonaktifkan semua paket diamond terkait.')"><i class="fas fa-trash-alt"></i> Nonaktifkan</button>
                                            </form>
                                        <?php endif; ?>
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
    <script src="../script.js"></script>
</body>

</html>
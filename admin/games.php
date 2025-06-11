<?php
session_start();
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $db->query('INSERT INTO games (name, image, description) VALUES (:name, :image, :description)');
                $db->bind(':name', $_POST['name']);
                $db->bind(':image', $_POST['image']);
                $db->bind(':description', $_POST['description']);
                $db->execute();
                $success = "Game berhasil ditambahkan!";
                break;

            case 'edit':
                $db->query('UPDATE games SET name = :name, image = :image, description = :description WHERE id = :id');
                $db->bind(':name', $_POST['name']);
                $db->bind(':image', $_POST['image']);
                $db->bind(':description', $_POST['description']);
                $db->bind(':id', $_POST['id']);
                $db->execute();
                $success = "Game berhasil diupdate!";
                break;

            case 'delete':
                $db->query('UPDATE games SET status = "inactive" WHERE id = :id');
                $db->bind(':id', $_POST['id']);
                $db->execute();
                $success = "Game berhasil dihapus!";
                break;
        }
    }
}

// Get all games
$db->query('SELECT * FROM games ORDER BY created_at DESC');
$games = $db->resultset();

// Get game for editing
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
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-section {
            background: #555555;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .form-section input,
        .form-section textarea {
            width: 100%;
            padding: 15px 5px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
        }

        .form-section textarea {
            height: 100px;
            resize: vertical;
        }

        .games-table {
            background: #555555;
            border-radius: 10px;
            overflow: hidden;
        }

        .games-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .games-table th,
        .games-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #666;
        }

        .games-table th {
            background: #333;
        }

        .games-table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .btn {
            padding: 8px 15px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #ff4b2b;
            color: white;
        }

        .btn-warning {
            background: #ff9800;
            color: white;
        }

        .btn-danger {
            background: #f44336;
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #4CAF50;
            color: white;
        }
    </style>
</head>

<body>
    <header>
        <h1 class="fade-in">Kelola Games</h1>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="transactions.php">Transaksi</a>
            <a href="../index.php">Beranda</a>
        </nav>
    </header>

    <div class="admin-container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h3><?php echo $edit_game ? 'Edit Game' : 'Tambah Game Baru'; ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_game ? 'edit' : 'add'; ?>">
                <?php if ($edit_game): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_game['id']; ?>">
                <?php endif; ?>

                <input type="text" name="name" placeholder="Nama Game"
                    value="<?php echo $edit_game ? $edit_game['name'] : ''; ?>" required>

                <input type="text" name="image" placeholder="Path Gambar (contoh: gambar/game.jpg)"
                    value="<?php echo $edit_game ? $edit_game['image'] : ''; ?>" required>

                <textarea name="description" placeholder="Deskripsi Game" required><?php echo $edit_game ? $edit_game['description'] : ''; ?></textarea>

                <button type="submit" class="btn btn-primary">
                    <?php echo $edit_game ? 'Update Game' : 'Tambah Game'; ?>
                </button>

                <?php if ($edit_game): ?>
                    <a href="games.php" class="btn btn-warning">Batal</a>
                <?php endif; ?>
            </form>
        </div>

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
                    <?php foreach ($games as $game): ?>
                        <tr>
                            <td><?php echo $game['id']; ?></td>
                            <td><img src="../<?php echo $game['image']; ?>" alt="<?php echo $game['name']; ?>"></td>
                            <td><?php echo $game['name']; ?></td>
                            <td><?php echo substr($game['description'], 0, 100) . '...'; ?></td>
                            <td>
                                <span style="color: <?php echo $game['status'] == 'active' ? '#4CAF50' : '#f44336'; ?>">
                                    <?php echo ucfirst($game['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="games.php?edit=<?php echo $game['id']; ?>" class="btn btn-warning">Edit</a>
                                <?php if ($game['status'] == 'active'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $game['id']; ?>">
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus game ini?')">Hapus</button>
                                    </form>
                                <?php endif; ?>
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
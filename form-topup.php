<?php
session_start();
require_once 'config/database.php';

$game_id = $_GET['game_id'] ?? 1;

// Get game details
$db->query('SELECT * FROM games WHERE id = :id');
$db->bind(':id', $game_id);
$game = $db->single();

// Get diamond packages for this game
$db->query('SELECT * FROM diamond_packages WHERE game_id = :game_id AND status = "active"');
$db->bind(':game_id', $game_id);
$packages = $db->resultset();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_POST['userid'];
    $server = $_POST['server'];
    $package_id = $_POST['jumlah'];

    // Get package details
    $db->query('SELECT * FROM diamond_packages WHERE id = :id');
    $db->bind(':id', $package_id);
    $package = $db->single();

    // Store in session for next step
    $_SESSION['topup_data'] = [
        'userid' => $userid,
        'server' => $server,
        'game_id' => $game_id,
        'game_name' => $game['name'],
        'package_id' => $package_id,
        'diamond_amount' => $package['amount'],
        'price' => $package['price']
    ];

    header('Location: metode-pembayaran.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Form Top Up</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Form Top Up - <?php echo $game['name']; ?></h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="daftar-game.php">Daftar Game</a>
        </nav>
    </header>

    <main>
        <form class="topup-form" method="POST">
            <label for="userid">User ID:</label>
            <input type="number" id="userid" name="userid" required />

            <label for="server">Server:</label>
            <input type="text" id="server" name="server" required />

            <label for="jumlah">Jumlah Top Up:</label>
            <select id="jumlah" name="jumlah" required>
                <?php foreach ($packages as $package): ?>
                    <option value="<?php echo $package['id']; ?>">
                        <?php echo $package['amount']; ?> Diamonds - Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-topup">Lanjut Pembayaran</button>
        </form>
    </main>

    <footer>&copy; 2025 Top Up Game. All rights reserved.</footer>
    <script src="script.js"></script>
</body>

</html>
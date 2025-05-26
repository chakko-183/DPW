<?php
require_once 'config/database.php';
$db->query('SELECT * FROM games WHERE status = "active" ORDER BY name');
$games = $db->resultset();

$gambar = ['feature-icon-ml', 'feature-icon-PUBG', 'feature-icon-ff', 'feature-icon-genshin'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Game</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <header>
        <h1 class="fade-in">Daftar Game</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="riwayat.php">Riwayat</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="loading-container">Loading</div>

    <main>
        <div class="game">
            <h1>TOP UP GAME</h1>
            <ul class="game-list">
                <?php foreach ($games as $game): ?>
                <li>
                    <div class="card">
                        <a href="form-topup.php?game_id=<?php echo $game['id']; ?>">
                            <img src="<?php echo htmlspecialchars($game['image']); ?>" alt="<?php echo htmlspecialchars($game['name']); ?>">
                        </a>
                        <p><?php echo htmlspecialchars($game['name']); ?></p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="container">
            <section class="features">
                <div class="feature-row">
                    <?php 
                    $count = 0;
                    foreach ($games as $key => $game): 
                        if ($count % 2 == 0 && $count > 0): ?>
                </div>
                <div class="feature-row">
                        <?php endif; ?>
                    <div class="feature">
                        <div class="<?= $gambar[$key] ?>">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="feature-content">
                            <h3><?php echo htmlspecialchars($game['name']); ?></h3>
                            <p><?php echo htmlspecialchars($game['description']); ?></p>
                        </div>
                    </div>
                    <?php 
                    $count++;
                    endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <footer>&copy; 2025 Top Up Game. All rights reserved.</footer>
    <script src="script.js"></script>
</body>
</html>
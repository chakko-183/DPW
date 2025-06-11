<?php
session_start();
require_once 'config/database.php'; // Pastikan path ini benar

$message = '';
$message_type = '';

// Handle review submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    header('Content-Type: application/json'); // Respond with JSON

    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $review_text = filter_input(INPUT_POST, 'review_text', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES); // Sanitize string

    // Get username from session if logged in, otherwise use a placeholder
    $username = 'Anonim'; // Default username
    $user_id = null; // Default user_id

    if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['email']; // Menggunakan email sebagai username, bisa diganti dengan nama user jika ada di tabel users
    }

    // Validasi input di sisi server
    if ($rating >= 1 && $rating <= 5 && !empty($review_text)) {
        try {
            $db->query('INSERT INTO reviews (user_id, username, rating, review_text, status, created_at) VALUES (:user_id, :username, :rating, :review_text, "pending", NOW())');
            $db->bind(':user_id', $user_id);
            $db->bind(':username', $username);
            $db->bind(':rating', $rating);
            $db->bind(':review_text', $review_text);

            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'Ulasan Anda berhasil dikirim dan akan diverifikasi oleh admin.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ulasan ke database.']);
            }
        } catch (PDOException $e) {
            // Log error untuk debugging
            error_log("Review submission PDO error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server saat menyimpan ulasan.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Rating tidak valid atau ulasan kosong.']);
    }
    exit(); // Sangat penting untuk menghentikan eksekusi script setelah respons AJAX
}

// Fetch approved reviews from the database
try {
    $db->query('SELECT username, rating, review_text, created_at FROM reviews WHERE status = "approved" ORDER BY created_at DESC');
    $approved_reviews = $db->resultset();
} catch (PDOException $e) {
    error_log("Failed to fetch approved reviews: " . $e->getMessage());
    $approved_reviews = []; // Set empty array if fetching fails
    $message = "Gagal memuat ulasan. Terjadi kesalahan database.";
    $message_type = "danger";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ulasan & Rating</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="loading-container">Loading</div>

    <header>
        <h1 class="fade-in">Ulasan & Rating</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="daftar-game.php">Daftar Game</a>
            <a href="riwayat.php">Riwayat</a>
            <a href="wishlist.php">wishlist</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin/index.php">Admin Panel</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="fade-in">
        <section class="review-section">
            <h2>Berikan Ulasan Anda</h2>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-danger">
                    Anda harus <a href="index.php" class="alert-link">login</a> untuk memberikan ulasan.
                </div>
            <?php else: ?>
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form id="review-form" class="review-form">
                    <div class="input-group">
                        <label>Berikan Rating:</label>
                        <div class="rating-stars" id="rating-stars">
                            <span class="star" data-value="1">&#9733;</span>
                            <span class="star" data-value="2">&#9733;</span>
                            <span class="star" data-value="3">&#9733;</span>
                            <span class="star" data-value="4">&#9733;</span>
                            <span class="star" data-value="5">&#9733;</span>
                        </div>
                        <input type="hidden" id="selected-rating" name="rating" value="0" required>
                    </div>

                    <div class="input-group">
                        <label for="review-text">Ulasan Anda:</label>
                        <textarea id="review-text" name="review_text" placeholder="Tulis ulasan Anda..." required></textarea>
                    </div>

                    <button type="submit" class="btn-primary">Kirim Ulasan</button>
                </form>
            <?php endif; ?>
        </section>

        <section id="reviews-list" class="reviews-list">
            <h2>Ulasan Pengguna Lain</h2>

            <?php if (empty($approved_reviews)): ?>
                <p class="no-records">Belum ada ulasan yang disetujui.</p>
            <?php else: ?>
                <?php foreach ($approved_reviews as $review): ?>
                    <div class="review-item">
                        <div class="stars"><?php echo str_repeat('&#9733;', $review['rating']); ?></div>
                        <p class="review-meta">Dari: <strong><?php echo htmlspecialchars($review['username']); ?></strong> pada <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></p>
                        <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="footer-section">
            <h4>Tentang Kami</h4>
            <div class="footer-content">
                <p>© 2025 Top Up Game. All rights reserved.</p>
            </div>
        </div>

        <div class="footer-section">
            <h4>Bantuan & Informasi</h4>
            <a href="bantuan_masalah.php" class="footer-link">🛠 Bantuan & Masalah</a>
            <a href="faq.html" class="footer-link">Halaman FAQ</a>
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
    </footer>
    <script src="script.js"></script>
</body>

</html>
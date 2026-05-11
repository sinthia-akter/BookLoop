<?php
require_once 'includes/config.php';

// Get featured/latest books
$sql = "SELECT * FROM books ORDER BY created_at DESC LIMIT 12";
$result = mysqli_query($conn, $sql);

require_once 'includes/header.php';
?>

<div class="hero-section">
    <h1>Welcome to BookLoop</h1>
    <p>Discover amazing books at amazing prices</p>
    <div style="margin-top: 30px;">
        <a href="search.php" class="btn" style="background: white; color: #667eea;">Browse All Books</a>
    </div>
</div>

<h2>Latest Books</h2>
<div class="book-grid">
    <?php if(mysqli_num_rows($result) == 0): ?>
        <p>No books available yet.</p>
    <?php else: ?>
        <?php while($book = mysqli_fetch_assoc($result)): ?>
            <div class="book-card">
                <div class="book-cover">
                    <?php if($book['cover_image'] && file_exists($book['cover_image'])): ?>
                        <img src="<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>">
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px;">📖<br>No Image</div>
                    <?php endif; ?>
                </div>
                <div class="book-info">
                    <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="book-price">৳<?php echo number_format($book['price'], 2); ?></p>
                    <span class="book-condition"><?php echo ucfirst(str_replace('_', ' ', $book['book_condition'])); ?></span>
                    <div style="margin-top: 15px;">
                        <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn" style="background: #3498db;">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
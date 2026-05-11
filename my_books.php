<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'bookstore_owner') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Get all books by this seller
$sql = "SELECT * FROM books WHERE seller_id = $seller_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

require_once 'includes/header.php';
?>

<h2>My Books</h2>
<p>Manage your book listings</p>
<div style="margin: 20px 0;">
    <a href="add_book.php" class="btn">+ Add New Book</a>
</div>

<?php if(mysqli_num_rows($result) == 0): ?>
    <div class="alert alert-info">You haven't added any books yet. Click "Add New Book" to get started!</div>
<?php else: ?>
    <div class="book-grid">
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
                        <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn" style="background: #3498db;">Edit</a>
                        <a href="delete_book.php?id=<?php echo $book['id']; ?>" class="btn btn-danger" onclick="return confirmDelete()">Delete</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
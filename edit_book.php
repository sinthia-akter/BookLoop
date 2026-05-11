<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'bookstore_owner') {
    header("Location: login.php");
    exit();
}

$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if book belongs to this seller
$sql = "SELECT * FROM books WHERE id = $book_id AND seller_id = " . $_SESSION['user_id'];
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: my_books.php");
    exit();
}

$book = mysqli_fetch_assoc($result);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $condition = mysqli_real_escape_string($conn, $_POST['condition']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);

    // Handle image upload
    $image_path = $book['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['cover_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_path = 'assets/uploads/books/' . $new_filename;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if ($book['cover_image'] && file_exists($book['cover_image'])) {
                    unlink($book['cover_image']);
                }
                $image_path = $upload_path;
            } else {
                $error = "Failed to upload image!";
            }
        } else {
            $error = "Only JPG, PNG, GIF files are allowed!";
        }
    }

    if (empty($error)) {
        $update_sql = "UPDATE books SET 
                        title='$title',
                        author='$author',
                        isbn='$isbn',
                        price='$price',
                        book_condition='$condition',
                        description='$description',
                        genre='$genre',
                        cover_image='$image_path'
                        WHERE id=$book_id";
        
        if (mysqli_query($conn, $update_sql)) {
            $success = "Book updated successfully!";
            // Refresh book data
            $result = mysqli_query($conn, $sql);
            $book = mysqli_fetch_assoc($result);
        } else {
            $error = "Failed to update book: " . mysqli_error($conn);
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-container">
    <h2>Edit Book</h2>
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Book Title *</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
        </div>
        <div class="form-group">
            <label>Author *</label>
            <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
        </div>
        <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
        </div>
        <div class="form-group">
            <label>Price (BDT) *</label>
            <input type="number" step="0.01" name="price" value="<?php echo $book['price']; ?>" required>
        </div>
        <div class="form-group">
            <label>Condition *</label>
            <select name="condition" required>
                <option value="new" <?php echo $book['book_condition'] == 'new' ? 'selected' : ''; ?>>New</option>
                <option value="like_new" <?php echo $book['book_condition'] == 'like_new' ? 'selected' : ''; ?>>Like New</option>
                <option value="good" <?php echo $book['book_condition'] == 'good' ? 'selected' : ''; ?>>Good</option>
                <option value="fair" <?php echo $book['book_condition'] == 'fair' ? 'selected' : ''; ?>>Fair</option>
            </select>
        </div>
        <div class="form-group">
            <label>Genre</label>
            <select name="genre">
                <option value="">Select genre</option>
                <option value="fiction" <?php echo $book['genre'] == 'fiction' ? 'selected' : ''; ?>>Fiction</option>
                <option value="non-fiction" <?php echo $book['genre'] == 'non-fiction' ? 'selected' : ''; ?>>Non-Fiction</option>
                <option value="mystery" <?php echo $book['genre'] == 'mystery' ? 'selected' : ''; ?>>Mystery</option>
                <option value="romance" <?php echo $book['genre'] == 'romance' ? 'selected' : ''; ?>>Romance</option>
                <option value="science" <?php echo $book['genre'] == 'science' ? 'selected' : ''; ?>>Science</option>
                <option value="history" <?php echo $book['genre'] == 'history' ? 'selected' : ''; ?>>History</option>
                <option value="children" <?php echo $book['genre'] == 'children' ? 'selected' : ''; ?>>Children</option>
                <option value="textbook" <?php echo $book['genre'] == 'textbook' ? 'selected' : ''; ?>>Textbook</option>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5"><?php echo htmlspecialchars($book['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Current Cover Image</label>
            <?php if($book['cover_image'] && file_exists($book['cover_image'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="<?php echo $book['cover_image']; ?>" style="max-width: 150px;">
                </div>
            <?php endif; ?>
            <input type="file" name="cover_image" accept="image/*">
            <small>Leave empty to keep current image</small>
        </div>
        <button type="submit" class="btn">Update Book</button>
        <a href="my_books.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
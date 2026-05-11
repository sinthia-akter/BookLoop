<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'bookstore_owner') {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Create uploads directory if not exists
if (!file_exists('assets/uploads/books/')) {
    mkdir('assets/uploads/books/', 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $condition = mysqli_real_escape_string($conn, $_POST['condition']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
    $seller_id = $_SESSION['user_id'];

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['cover_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_path = 'assets/uploads/books/' . $new_filename;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            } else {
                $error = "Failed to upload image!";
            }
        } else {
            $error = "Only JPG, PNG, GIF files are allowed!";
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO books (title, author, isbn, price, book_condition, description, genre, seller_id, cover_image, created_at) 
                VALUES ('$title', '$author', '$isbn', '$price', '$condition', '$description', '$genre', '$seller_id', '$image_path', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Book added successfully!";
            $_POST = array();
        } else {
            $error = "Failed to add book: " . mysqli_error($conn);
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-container">
    <h2>Add New Book</h2>
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Book Title *</label>
            <input type="text" name="title" value="<?php echo isset($_POST['title']) ? $_POST['title'] : ''; ?>" required>
        </div>
        <div class="form-group">
            <label>Author *</label>
            <input type="text" name="author" value="<?php echo isset($_POST['author']) ? $_POST['author'] : ''; ?>" required>
        </div>
        <div class="form-group">
            <label>ISBN (Optional)</label>
            <input type="text" name="isbn" value="<?php echo isset($_POST['isbn']) ? $_POST['isbn'] : ''; ?>">
        </div>
        <div class="form-group">
            <label>Price (BDT) *</label>
            <input type="number" step="0.01" name="price" value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" required>
        </div>
        <div class="form-group">
            <label>Condition *</label>
            <select name="condition" required>
                <option value="">Select condition</option>
                <option value="new">New</option>
                <option value="like_new">Like New</option>
                <option value="good">Good</option>
                <option value="fair">Fair</option>
            </select>
        </div>
        <div class="form-group">
            <label>Genre</label>
            <select name="genre">
                <option value="">Select genre</option>
                <option value="fiction">Fiction</option>
                <option value="non-fiction">Non-Fiction</option>
                <option value="mystery">Mystery</option>
                <option value="romance">Romance</option>
                <option value="science">Science</option>
                <option value="history">History</option>
                <option value="children">Children</option>
                <option value="textbook">Textbook</option>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5"><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
        </div>
        <div class="form-group">
            <label>Cover Image</label>
            <input type="file" name="cover_image" accept="image/*">
            <small style="color: #666;">Upload JPG, PNG or GIF (Max 2MB)</small>
        </div>
        <button type="submit" class="btn">Add Book</button>
        <a href="my_books.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
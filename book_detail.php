<?php
require_once 'includes/config.php';

$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT b.*, u.username, u.email FROM books b 
        LEFT JOIN users u ON b.seller_id = u.id 
        WHERE b.id = $book_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$book = mysqli_fetch_assoc($result);

// Add to cart action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $quantity = intval($_POST['quantity']);
    
    // Check if already in cart
    $check_cart = "SELECT * FROM cart WHERE user_id = " . $_SESSION['user_id'] . " AND book_id = $book_id";
    $cart_result = mysqli_query($conn, $check_cart);
    
    if (mysqli_num_rows($cart_result) > 0) {
        // Update quantity
        $cart_item = mysqli_fetch_assoc($cart_result);
        $new_qty = $cart_item['quantity'] + $quantity;
        $update_cart = "UPDATE cart SET quantity = $new_qty WHERE id = " . $cart_item['id'];
        mysqli_query($conn, $update_cart);
    } else {
        // Add to cart
        $insert_cart = "INSERT INTO cart (user_id, book_id, quantity, added_at) 
                        VALUES (" . $_SESSION['user_id'] . ", $book_id, $quantity, NOW())";
        mysqli_query($conn, $insert_cart);
    }
    
    $_SESSION['cart_message'] = "Book added to cart!";
    header("Location: book_detail.php?id=$book_id");
    exit();
}

require_once 'includes/header.php';
?>

<div class="book-detail">
    <div style="flex: 1;">
        <div style="background: #f5f5f5; padding: 20px; text-align: center; border-radius: 10px;">
            <?php if($book['cover_image'] && file_exists($book['cover_image'])): ?>
                <img src="<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>" style="max-width: 100%; max-height: 400px;">
            <?php else: ?>
                <div style="font-size: 5rem;">📖</div>
                <p>No cover image available</p>
            <?php endif; ?>
        </div>
    </div>
    <div style="flex: 2;">
        <h1 style="font-size: 2rem;"><?php echo htmlspecialchars($book['title']); ?></h1>
        <p style="color: #666; font-size: 1.2rem;">by <?php echo htmlspecialchars($book['author']); ?></p>
        
        <div style="margin: 20px 0;">
            <span class="book-condition" style="font-size: 1rem;"><?php echo ucfirst(str_replace('_', ' ', $book['book_condition'])); ?></span>
        </div>
        
        <p class="book-price" style="font-size: 2rem;">৳<?php echo number_format($book['price'], 2); ?></p>
        
        <?php if($book['genre']): ?>
            <p><strong>Genre:</strong> <?php echo ucfirst($book['genre']); ?></p>
        <?php endif; ?>
        
        <?php if($book['isbn']): ?>
            <p><strong>ISBN:</strong> <?php echo $book['isbn']; ?></p>
        <?php endif; ?>
        
        <div style="margin: 20px 0;">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
        </div>
        
        <div style="margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px;">
            <p><strong>Seller:</strong> <?php echo htmlspecialchars($book['username']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($book['email']); ?></p>
        </div>
        
        <form method="POST" action="" style="margin-top: 30px;">
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" value="1" min="1" style="width: 80px;">
            </div>
            <button type="submit" name="add_to_cart" class="btn">Add to Cart 🛒</button>
        </form>
        
        <?php if(isset($_SESSION['cart_message'])): ?>
            <div class="alert alert-success" style="margin-top: 20px;">
                <?php 
                    echo $_SESSION['cart_message'];
                    unset($_SESSION['cart_message']);
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Update quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $qty) {
        $cart_id = intval($cart_id);
        $qty = intval($qty);
        if ($qty > 0) {
            $update = "UPDATE cart SET quantity = $qty WHERE id = $cart_id AND user_id = $user_id";
            mysqli_query($conn, $update);
        }
    }
    header("Location: cart.php");
    exit();
}

// Remove item
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $delete = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
    mysqli_query($conn, $delete);
    header("Location: cart.php");
    exit();
}

// Get cart items
$sql = "SELECT c.*, b.title, b.author, b.price, b.cover_image 
        FROM cart c 
        JOIN books b ON c.book_id = b.id 
        WHERE c.user_id = $user_id";
$result = mysqli_query($conn, $sql);

$total = 0;

require_once 'includes/header.php';
?>

<h2>Shopping Cart</h2>

<?php if(mysqli_num_rows($result) == 0): ?>
    <div class="alert alert-info">Your cart is empty. <a href="index.php">Continue shopping</a></div>
<?php else: ?>
    <form method="POST" action="">
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = mysqli_fetch_assoc($result)): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if($item['cover_image'] && file_exists($item['cover_image'])): ?>
                                        <img src="<?php echo $item['cover_image']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                        by <?php echo htmlspecialchars($item['author']); ?>
                                    </div>
                                </div>
                            </td>
                            <td>৳<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" min="1" style="width: 60px;">
                            </td>
                            <td>৳<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <a href="?remove=<?php echo $item['id']; ?>" class="btn btn-danger" onclick="return confirmDelete()">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; font-size: 1.2rem;">
                        <td colspan="3" align="right">Total:</td>
                        <td>৳<?php echo number_format($total, 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div style="margin-top: 20px; text-align: right;">
            <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
            <a href="checkout.php" class="btn">Proceed to Checkout</a>
        </div>
    </form>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
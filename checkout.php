<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items
$sql = "SELECT c.*, b.title, b.price, b.seller_id 
        FROM cart c 
        JOIN books b ON c.book_id = b.id 
        WHERE c.user_id = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: cart.php");
    exit();
}

$total = 0;
$items = [];
while($item = mysqli_fetch_assoc($result)) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $items[] = $item;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Create order
    $order_sql = "INSERT INTO orders (user_id, total_amount, shipping_address, phone, payment_method, order_status, created_at) 
                  VALUES ($user_id, $total, '$address', '$phone', '$payment_method', 'pending', NOW())";
    
    if (mysqli_query($conn, $order_sql)) {
        $order_id = mysqli_insert_id($conn);
        
        // Add order items
        foreach ($items as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $order_item_sql = "INSERT INTO order_items (order_id, book_id, quantity, price, seller_id) 
                              VALUES ($order_id, {$item['book_id']}, {$item['quantity']}, {$item['price']}, {$item['seller_id']})";
            mysqli_query($conn, $order_item_sql);
        }
        
        // Clear cart
        $clear_cart = "DELETE FROM cart WHERE user_id = $user_id";
        mysqli_query($conn, $clear_cart);
        
        // Store order ID for success page
        $_SESSION['last_order_id'] = $order_id;
        header("Location: order_success.php");
        exit();
    } else {
        $error = "Failed to place order: " . mysqli_error($conn);
    }
}

require_once 'includes/header.php';
?>

<div class="form-container">
    <h2>Checkout</h2>
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="order-summary" style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3>Order Summary</h3>
        <table style="width: 100%;">
            <?php foreach($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?> x <?php echo $item['quantity']; ?></td>
                    <td align="right">৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; border-top: 2px solid #ddd;">
                <td>Total:</td>
                <td align="right">৳<?php echo number_format($total, 2); ?></td>
            </tr>
        </table>
    </div>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Shipping Address *</label>
            <textarea name="address" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label>Phone Number *</label>
            <input type="tel" name="phone" required>
        </div>
        <div class="form-group">
            <label>Payment Method *</label>
            <select name="payment_method" required>
                <option value="">Select payment method</option>
                <option value="cod">Cash on Delivery</option>
                <option value="bkash">bKash</option>
                <option value="nagad">Nagad</option>
                <option value="rocket">Rocket</option>
            </select>
        </div>
        <div class="form-group" id="mobile_payment_info" style="display: none; background: #f0f8ff; padding: 10px; border-radius: 5px;">
            <p><strong>Payment Instructions:</strong></p>
            <p>Send payment to: 01XXXXXXXXX (bKash/Nagad/Rocket)</p>
            <p>After payment, you'll receive confirmation via SMS</p>
        </div>
        <button type="submit" class="btn" style="width: 100%;">Place Order</button>
    </form>
</div>

<script>
document.querySelector('select[name="payment_method"]').addEventListener('change', function() {
    const infoDiv = document.getElementById('mobile_payment_info');
    if (this.value !== '' && this.value !== 'cod') {
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
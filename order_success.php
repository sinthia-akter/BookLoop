<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_SESSION['last_order_id']) ? $_SESSION['last_order_id'] : 0;

if ($order_id == 0) {
    header("Location: index.php");
    exit();
}

// Get order details
$order_sql = "SELECT * FROM orders WHERE id = $order_id AND user_id = " . $_SESSION['user_id'];
$order_result = mysqli_query($conn, $order_sql);
$order = mysqli_fetch_assoc($order_result);

// Get order items
$items_sql = "SELECT oi.*, b.title, b.author 
              FROM order_items oi 
              JOIN books b ON oi.book_id = b.id 
              WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_sql);

require_once 'includes/header.php';
?>

<div style="text-align: center; padding: 40px;">
    <div style="font-size: 5rem;">🎉</div>
    <h1>Order Placed Successfully!</h1>
    <p style="font-size: 1.2rem; margin: 20px 0;">Thank you for your order.</p>
    
    <div class="order-details" style="background: white; padding: 20px; border-radius: 10px; text-align: left; max-width: 600px; margin: 30px auto;">
        <h3>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
        <p><strong>Total Amount:</strong> ৳<?php echo number_format($order['total_amount'], 2); ?></p>
        <p><strong>Payment Method:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
        <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
        <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
        
        <h3 style="margin-top: 20px;">Items Ordered:</h3>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>৳<?php echo number_format($item['price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="index.php" class="btn">Continue Shopping</a>
        <a href="profile.php" class="btn btn-secondary">View My Orders</a>
    </div>
</div>

<?php
// Clear the session variable
unset($_SESSION['last_order_id']);
require_once 'includes/footer.php';
?>
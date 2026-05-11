<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$order_sql = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = $order_id";
$order_result = mysqli_query($conn, $order_sql);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get order items
$items_sql = "SELECT oi.*, b.title, b.author FROM order_items oi JOIN books b ON oi.book_id = b.id WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_sql);

require_once '../includes/header.php';
?>

<div style="background: white; padding: 20px; border-radius: 10px;">
    <h2>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h2>
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
    <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
    <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
    <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
    <p><strong>Payment Method:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
    <p><strong>Status:</strong> <?php echo ucfirst($order['order_status']); ?></p>
    
    <h3 style="margin-top: 20px;">Order Items</h3>
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Author</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo htmlspecialchars($item['author']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>৳<?php echo number_format($item['price'], 2); ?></td>
                        <td>৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold;">
                    <td colspan="4" align="right">Total:</td>
                    <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
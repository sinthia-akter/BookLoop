<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Update order status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $update = "UPDATE orders SET order_status = '$status' WHERE id = $order_id";
    mysqli_query($conn, $update);
}

// Get all orders
$orders = mysqli_query($conn, "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");

require_once '../includes/header.php';
?>

<h2>Manage Orders</h2>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
                <th>Items</th>
            </tr>
        </thead>
        <tbody>
            <?php while($order = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo strtoupper($order['payment_method']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <input type="submit" name="update_status" value="Update" style="display: none;">
                        </form>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    <td>
                        <a href="order_items.php?id=<?php echo $order['id']; ?>" class="btn" style="background: #3498db; padding: 5px 10px;">View Items</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
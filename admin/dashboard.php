<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$users_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$books_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM books"))['total'];
$orders_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
$revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE order_status != 'cancelled'"))['total'];

// Recent orders
$recent_orders = mysqli_query($conn, "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10");

require_once '../includes/header.php';
?>

<div class="admin-dashboard">
    <h2>Admin Dashboard</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-number"><?php echo $users_count; ?></div>
            <p>Total Users</p>
            <a href="users.php">Manage Users →</a>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-number"><?php echo $books_count; ?></div>
            <p>Total Books</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-number"><?php echo $orders_count; ?></div>
            <p>Total Orders</p>
            <a href="orders.php">Manage Orders →</a>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-number">৳<?php echo number_format($revenue ?: 0, 2); ?></div>
            <p>Total Revenue</p>
        </div>
    </div>
    
    <div style="background: white; padding: 20px; border-radius: 10px;">
        <h3>Recent Orders</h3>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span style="padding: 3px 8px; background: <?php 
                                    echo $order['order_status'] == 'delivered' ? '#d4edda' : ($order['order_status'] == 'cancelled' ? '#f8d7da' : '#fff3cd'); 
                                ?>; border-radius: 3px;">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><a href="orders.php">View</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
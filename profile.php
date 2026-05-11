<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $update_sql = "UPDATE users SET username='$username', email='$email' WHERE id=$user_id";
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $success = "Profile updated successfully!";
        // Refresh user data
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);
    } else {
        $error = "Update failed!";
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if (password_verify($current, $user['password'])) {
        if ($new == $confirm) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update_pass = "UPDATE users SET password='$hashed' WHERE id=$user_id";
            if (mysqli_query($conn, $update_pass)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Password change failed!";
            }
        } else {
            $error = "New passwords don't match!";
        }
    } else {
        $error = "Current password is incorrect!";
    }
}

// Get user's orders
$orders_sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_sql);

require_once 'includes/header.php';
?>

<div class="form-container" style="max-width: 800px;">
    <h2>My Profile</h2>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <h3>Account Information</h3>
    <form method="POST" action="">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?php echo $user['username']; ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Member Since</label>
            <input type="text" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" disabled>
        </div>
        <button type="submit" name="update" class="btn">Update Profile</button>
    </form>
    
    <hr style="margin: 30px 0;">
    
    <h3>Change Password</h3>
    <form method="POST" action="">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required>
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit" name="change_password" class="btn">Change Password</button>
    </form>
    
    <hr style="margin: 30px 0;">
    
    <h3>My Orders</h3>
    <?php if(mysqli_num_rows($orders_result) == 0): ?>
        <p>You haven't placed any orders yet.</p>
    <?php else: ?>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo ucfirst($order['order_status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
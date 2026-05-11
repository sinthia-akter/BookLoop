<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $delete_id");
    }
    header("Location: users.php");
    exit();
}

// Handle role update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    mysqli_query($conn, "UPDATE users SET role = '$role' WHERE id = $user_id");
}

// Get all users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

require_once '../includes/header.php';
?>

<h2>Manage Users</h2>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <select name="role" onchange="this.form.submit()">
                                <option value="customer" <?php echo $user['role'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                                <option value="bookstore_owner" <?php echo $user['role'] == 'bookstore_owner' ? 'selected' : ''; ?>>Bookstore Owner</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <input type="submit" name="update_role" value="Update" style="display: none;">
                        </form>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirmDelete()">Delete</a>
                        <?php else: ?>
                            <span style="color: #999;">Current User</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
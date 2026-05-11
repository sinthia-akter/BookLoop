<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Validation
    if ($password != $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if user exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users (username, email, password, role, created_at) 
                    VALUES ('$username', '$email', '$hashed_password', '$role', NOW())";

            if (mysqli_query($conn, $sql)) {
                $success = "Registration successful! Please login.";
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="form-container">
    <h2>Create Account</h2>
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>
        <div class="form-group">
            <label>I am a:</label>
            <select name="role">
                <option value="customer">Customer (Buy books)</option>
                <option value="bookstore_owner">Bookstore Owner (Sell books)</option>
            </select>
        </div>
        <button type="submit" class="btn">Register</button>
        <p style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
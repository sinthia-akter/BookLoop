<?php
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] == 'bookstore_owner') {
                header("Location: my_books.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not found!";
    }
}

require_once 'includes/header.php';
?>

<div class="form-container">
    <h2>Login to BookLoop</h2>
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
        <p style="margin-top: 15px;">Don't have an account? <a href="register.php">Register here</a></p>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
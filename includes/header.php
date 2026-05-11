<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookLoop - Thrift Bookstore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <div class="logo">
            <a href="index.php">📚 BookLoop</a>
        </div>
        <div class="search-bar">
            <form action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search books...">
                <button type="submit">🔍</button>
            </form>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['role'] == 'bookstore_owner'): ?>
                    <a href="add_book.php">Add Book</a>
                    <a href="my_books.php">My Books</a>
                <?php endif; ?>
                <a href="cart.php">🛒 Cart</a>
                <a href="profile.php">👤 <?php echo $_SESSION['username']; ?></a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin/dashboard.php">⚙️ Admin</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="main-content">
    <div class="container">
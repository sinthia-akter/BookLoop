<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookLoop - Thrift Bookstore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <a href="../member3/index.php">📚 BookLoop</a>
            </div>
            <div class="search-bar">
                <form action="../member3/search.php" method="GET">
                    <input type="text" name="q" placeholder="Search books...">
                    <button type="submit">🔍</button>
                </form>
            </div>
            <div class="nav-links">
                <a href="../member3/index.php">Home</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] == 'bookstore_owner'): ?>
                        <a href="../member2/add_book.php">Add Book</a>
                        <a href="../member2/my_books.php">My Books</a>
                    <?php endif; ?>
                    <a href="../member2/cart.php">🛒 Cart</a>
                    <a href="../member1/profile.php">👤 <?php echo $_SESSION['username']; ?></a>
                    <a href="../member1/logout.php">Logout</a>
                <?php else: ?>
                    <a href="../member1/login.php">Login</a>
                    <a href="../member1/register.php">Register</a>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="../member1/admin/dashboard.php">⚙️ Admin</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <main class="main-content">
        <div class="container">

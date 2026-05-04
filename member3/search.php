<?php
require_once '../includes/config.php';

$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$genre = isset($_GET['genre']) ? mysqli_real_escape_string($conn, $_GET['genre']) : '';
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : 999999;
$condition = isset($_GET['condition']) ? mysqli_real_escape_string($conn, $_GET['condition']) : '';

// Build query
$sql = "SELECT * FROM books WHERE 1=1";

if ($search != '') {
    $sql .= " AND (title LIKE '%$search%' OR author LIKE '%$search%')";
}

if ($genre != '') {
    $sql .= " AND genre = '$genre'";
}

if ($min_price > 0) {
    $sql .= " AND price >= $min_price";
}

if ($max_price < 999999) {
    $sql .= " AND price <= $max_price";
}

if ($condition != '') {
    $sql .= " AND book_condition = '$condition'";
}

$sql .= " ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Get unique genres for filter
$genre_sql = "SELECT DISTINCT genre FROM books WHERE genre != ''";
$genre_result = mysqli_query($conn, $genre_sql);

require_once '../includes/header.php';
?>

<h2>Search Books</h2>

<div class="search-layout">
    <!-- Filters Sidebar -->
    <div class="sidebar">
        <h3>Filters</h3>
        <form method="GET" action="">
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Title or author">
            </div>
            
            <div class="form-group">
                <label>Genre</label>
                <select name="genre">
                    <option value="">All Genres</option>
                    <?php while($g = mysqli_fetch_assoc($genre_result)): ?>
                        <option value="<?php echo $g['genre']; ?>" <?php echo ($genre == $g['genre']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($g['genre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Price Range</label>
                <input type="number" name="min_price" placeholder="Min" value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                <input type="number" name="max_price" placeholder="Max" value="<?php echo $max_price < 999999 ? $max_price : ''; ?>" style="margin-top: 5px;">
            </div>
            
            <div class="form-group">
                <label>Condition</label>
                <select name="condition">
                    <option value="">Any Condition</option>
                    <option value="new" <?php echo $condition == 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="like_new" <?php echo $condition == 'like_new' ? 'selected' : ''; ?>>Like New</option>
                    <option value="good" <?php echo $condition == 'good' ? 'selected' : ''; ?>>Good</option>
                    <option value="fair" <?php echo $condition == 'fair' ? 'selected' : ''; ?>>Fair</option>
                </select>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Apply Filters</button>
            
            <a href="search.php" style="display: block; text-align: center; margin-top: 10px;">Clear Filters</a>
        </form>
    </div>
    
    <!-- Results -->
    <div class="results">
        <p>Found <?php echo mysqli_num_rows($result); ?> books</p>
        
        <div class="book-grid">
            <?php if(mysqli_num_rows($result) == 0): ?>
                <p>No books found matching your criteria.</p>
            <?php else: ?>
                <?php while($book = mysqli_fetch_assoc($result)): ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <?php if($book['cover_image'] && file_exists('../' . $book['cover_image'])): ?>
                                <img src="../<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>">
                            <?php else: ?>
                                <div style="text-align: center; padding: 40px;">📖<br>No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                            <p class="book-price">৳<?php echo number_format($book['price'], 2); ?></p>
                            <span class="book-condition"><?php echo ucfirst(str_replace('_', ' ', $book['book_condition'])); ?></span>
                            <div style="margin-top: 15px;">
                                <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn" style="background: #3498db;">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

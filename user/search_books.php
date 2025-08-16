<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle book request
if (isset($_POST['request_book'])) {
    $book_id = (int)$_POST['book_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if user already has a pending or approved request for this book
    $check = mysqli_query($conn, "SELECT status FROM book_issues 
                                 WHERE user_id = $user_id 
                                 AND book_id = $book_id 
                                 AND status IN ('pending', 'approved')");
    
    if ($existing = mysqli_fetch_assoc($check)) {
        $error = $existing['status'] === 'pending' 
            ? "You already have a pending request for this book." 
            : "You already have borrowed this book.";
    } else {
        // Create request with current date as requested_on
        $requested_on = date('Y-m-d');
        
        $sql = "INSERT INTO book_issues (book_id, user_id, requested_on, status) 
                VALUES ($book_id, $user_id, '$requested_on', 'pending')";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Book request submitted successfully!";
        } else {
            $error = "Error submitting request: " . mysqli_error($conn);
        }
    }
}

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$author = isset($_GET['author']) ? (int)$_GET['author'] : 0;

$sql = "SELECT b.*, c.name as category_name, a.name as author_name, r.name as rack_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id 
        LEFT JOIN authors a ON b.author_id = a.id 
        LEFT JOIN location_racks r ON b.rack_id = r.id 
        WHERE b.available_quantity > 0";

if ($search) {
    $sql .= " AND (b.title LIKE '%$search%' OR b.isbn LIKE '%$search%')";
}
if ($category) {
    $sql .= " AND b.category_id = $category";
}
if ($author) {
    $sql .= " AND b.author_id = $author";
}

$sql .= " ORDER BY b.title";
$books = mysqli_query($conn, $sql);

// Get categories and authors for filters
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$authors = mysqli_query($conn, "SELECT * FROM authors ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books - Library Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <?php include '../includes/alert_handler.php'; ?>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="container">
        <h1>Search Available Books</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <form method="GET">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label for="search">Search:</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by title or ISBN">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select name="category" id="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Author:</label>
                        <select name="author" id="author" class="form-control">
                            <option value="">All Authors</option>
                            <?php while ($auth = mysqli_fetch_assoc($authors)): ?>
                                <option value="<?php echo $auth['id']; ?>"
                                        <?php echo $author == $auth['id'] ? 'selected' : ''; ?>>
                                    <?php echo $auth['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="align-self: end;">
                        <button type="submit" class="btn">Search</button>
                        <a href="search_books.php" class="btn" style="background-color: #6c757d;">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Available Books</h2>
            
            <?php if (mysqli_num_rows($books) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Location</th>
                            <th>Available</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($books)): ?>
                        <tr>
                            <td><?php echo $book['title']; ?></td>
                            <td><?php echo $book['isbn']; ?></td>
                            <td><?php echo $book['category_name']; ?></td>
                            <td><?php echo $book['author_name']; ?></td>
                            <td><?php echo $book['rack_name']; ?></td>
                            <td><?php echo $book['available_quantity']; ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" name="request_book" class="btn">
                                        Request Book
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No books available matching your search criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

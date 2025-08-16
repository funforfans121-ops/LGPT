<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Get category details
$category_id = (int)$_GET['id'];
$category = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE id = $category_id"));

if (!$category) {
    header("Location: categories.php");
    exit();
}

// Get all books in this category
$sql = "SELECT b.*, a.name as author_name, r.name as rack_name 
        FROM books b 
        LEFT JOIN authors a ON b.author_id = a.id 
        LEFT JOIN location_racks r ON b.rack_id = r.id 
        WHERE b.category_id = $category_id 
        ORDER BY b.title";
$books = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category['name']; ?> Books - Library Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="books.php">Manage Books</a></li>
                <li><a href="users.php">Manage Users</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="authors.php">Authors</a></li>
                <li><a href="racks.php">Location Racks</a></li>
                <li><a href="issues.php">Book Issues</a></li>
                <li><a href="change_password.php">Change Password</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Books in <?php echo $category['name']; ?></h1>
        
        <div style="margin: 20px 0;">
            <a href="add_book.php?category_id=<?php echo $category_id; ?>&return_to=<?php echo urlencode("category_books.php?id=$category_id"); ?>" class="btn">Add New Book</a>
        </div>
        
        <div class="card">
            <?php if (mysqli_num_rows($books) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Rack</th>
                        <th>Quantity</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = mysqli_fetch_assoc($books)): ?>
                    <tr>
                        <td><?php echo $book['title']; ?></td>
                        <td><?php echo $book['author_name']; ?></td>
                        <td><?php echo $book['isbn']; ?></td>
                        <td><?php echo $book['rack_name']; ?></td>
                        <td><?php echo $book['quantity']; ?></td>
                        <td><?php echo $book['available_quantity']; ?></td>
                        <td>
                            <a href="edit_book.php?id=<?php echo $book['id']; ?>&return_to=<?php echo urlencode("category_books.php?id=$category_id"); ?>" class="btn">Edit</a>
                            <a href="book_history.php?id=<?php echo $book['id']; ?>&return_to=<?php echo urlencode("category_books.php?id=$category_id"); ?>" class="btn">View History</a>
                            <a href="books.php?action=delete&id=<?php echo $book['id']; ?>" 
                               class="btn" style="background-color: #dc3545;"
                               onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No books found in this category.</p>
            <?php endif; ?>
            
            <div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px;">
                <a href="categories.php" class="btn">Back</a>
            </div>
        </div>
    </div>
</body>
</html>

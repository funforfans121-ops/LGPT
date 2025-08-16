<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle book deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if book can be deleted
    $check = mysqli_query($conn, "SELECT id FROM book_issues WHERE book_id = $id AND status IN ('pending', 'approved')");
    if (mysqli_num_rows($check) > 0) {
        $error = "Cannot delete book: It has pending or active issues";
    } else {
        mysqli_query($conn, "DELETE FROM books WHERE id = $id");
        $success = "Book deleted successfully!";
    }
}

// Get all books with related info
$sql = "SELECT b.*, c.name as category_name, a.name as author_name, r.name as rack_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id 
        LEFT JOIN authors a ON b.author_id = a.id 
        LEFT JOIN location_racks r ON b.rack_id = r.id 
        ORDER BY b.title";
$books = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Library Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <?php include '../includes/alert_handler.php'; ?>
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
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Manage Books</h1>
        
        <div style="margin: 20px 0;">
            <a href="add_book.php" class="btn">Add New Book</a>
        </div>
        
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
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>ISBN</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Location</th>
                        <th>Total Quantity</th>
                        <th>Available</th>
                        <th>Actions</th>
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
                        <td><?php echo $book['quantity']; ?></td>
                        <td><?php echo $book['available_quantity']; ?></td>
                        <td>
                            <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn">Edit</a>
                            <a href="book_history.php?id=<?php echo $book['id']; ?>" class="btn">View History</a>
                            <?php if ($book['quantity'] == $book['available_quantity']): ?>
                                <a href="?delete=<?php echo $book['id']; ?>" 
                                   class="btn" 
                                   style="background-color: #dc3545;"
                                   onclick="return confirm('Are you sure you want to delete this book?')">
                                    Delete
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

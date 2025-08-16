<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];
$author = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM authors WHERE id = $id"));

if (!$author) {
    header("Location: authors.php");
    exit();
}

// Get all books by this author
$sql = "SELECT b.*, c.name as category_name, r.name as rack_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id 
        LEFT JOIN location_racks r ON b.rack_id = r.id 
        WHERE b.author_id = $id 
        ORDER BY b.title";
$books = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author Books - Library Management System</title>
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
        <h1>Books by <?php echo $author['name']; ?></h1>
        
        <div class="card">
            <h2>Author Information</h2>
            <p><strong>Name:</strong> <?php echo $author['name']; ?></p>
            <p><strong>Added on:</strong> <?php echo date('F j, Y', strtotime($author['created_at'])); ?></p>
            <p><strong>Total Books:</strong> <?php echo mysqli_num_rows($books); ?></p>
        </div>
        
        <div style="margin: 20px 0;">
            <a href="add_book.php?author_id=<?php echo $id; ?>&return_to=author_books.php?id=<?php echo $id; ?>" class="btn">Add New Book</a>
        </div>
        
        <div class="card">
            <h2>Book List</h2>
            
            <?php if (mysqli_num_rows($books) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>ISBN</th>
                            <th>Category</th>
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
                            <td><?php echo $book['rack_name']; ?></td>
                            <td><?php echo $book['quantity']; ?></td>
                            <td><?php echo $book['available_quantity']; ?></td>
                            <td>
                                <a href="edit_book.php?id=<?php echo $book['id']; ?>&return_to=author_books.php?id=<?php echo $id; ?>" class="btn">Edit</a>
                                <a href="book_history.php?id=<?php echo $book['id']; ?>&return_to=author_books.php?id=<?php echo $id; ?>" class="btn">View History</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No books found for this author.</p>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <a href="authors.php" class="btn">Back</a>
            </div>
        </div>
    </div>
</body>
</html>

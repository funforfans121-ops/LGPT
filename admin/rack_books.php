<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];
$rack = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM location_racks WHERE id = $id"));

if (!$rack) {
    header("Location: racks.php");
    exit();
}

// Get all books in this rack
$sql = "SELECT b.*, c.name as category_name, a.name as author_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id 
        LEFT JOIN authors a ON b.author_id = a.id 
        WHERE b.rack_id = $id 
        ORDER BY b.title";
$books = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rack Books - Library Management System</title>
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
        <h1>Books in <?php echo $rack['name']; ?></h1>
        
        <div class="card">
            <h2>Rack Information</h2>
            <p><strong>Location:</strong> <?php echo $rack['name']; ?></p>
            <p><strong>Added on:</strong> <?php echo date('F j, Y', strtotime($rack['created_at'])); ?></p>
            <p><strong>Total Books:</strong> <?php echo mysqli_num_rows($books); ?></p>
        </div>
        
        <div style="margin: 20px 0;">
            <a href="add_book.php?rack_id=<?php echo $id; ?>&return_to=<?php echo urlencode("rack_books.php?id=$id"); ?>" class="btn">Add New Book</a>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Book List</h2>
            
            <?php if (mysqli_num_rows($books) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Author</th>
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
                            <td><?php echo $book['quantity']; ?></td>
                            <td><?php echo $book['available_quantity']; ?></td>
                            <td>
                                <a href="edit_book.php?id=<?php echo $book['id']; ?>&return_to=rack_books.php?id=<?php echo $id; ?>" class="btn">Edit</a>
                                <a href="book_history.php?id=<?php echo $book['id']; ?>&return_to=rack_books.php?id=<?php echo $id; ?>" class="btn">View History</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No books found in this rack.</p>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <a href="racks.php" class="btn" style="background-color: #6c757d;">Back</a>
            </div>
        </div>
    </div>
</body>
</html>

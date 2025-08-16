<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle author addition
if (isset($_POST['add_author'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    
    $sql = "INSERT INTO authors (name) VALUES ('$name')";
    if (mysqli_query($conn, $sql)) {
        $success = "Author added successfully!";
    } else {
        $error = "Error adding author: " . mysqli_error($conn);
    }
}

// Handle author deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if author has any books
    $check = mysqli_query($conn, "SELECT id FROM books WHERE author_id = $id");
    if (mysqli_num_rows($check) > 0) {
        $error = "Cannot delete author: They have books in the library";
    } else {
        mysqli_query($conn, "DELETE FROM authors WHERE id = $id");
        $success = "Author deleted successfully!";
    }
}

// Get all authors with book counts
$authors = mysqli_query($conn, "SELECT a.*, COUNT(b.id) as book_count 
                               FROM authors a 
                               LEFT JOIN books b ON a.id = b.author_id 
                               GROUP BY a.id 
                               ORDER BY a.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Authors - Library Management System</title>
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
        <h1>Manage Authors</h1>
        
        <div class="card">
            <h2>Add New Author</h2>
            
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
            
            <form method="POST" style="max-width: 400px;">
                <div class="form-group">
                    <label for="name">Author Name:</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="add_author" class="btn">Add Author</button>
                </div>
            </form>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Existing Authors</h2>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Author Name</th>
                        <th>Books in Library</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($author = mysqli_fetch_assoc($authors)): ?>
                    <tr>
                        <td><?php echo $author['name']; ?></td>
                        <td><?php echo $author['book_count']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($author['created_at'])); ?></td>
                        <td>
                            <a href="edit_author.php?id=<?php echo $author['id']; ?>" class="btn">Edit</a>
                            <?php if ($author['book_count'] > 0): ?>
                                <a href="author_books.php?id=<?php echo $author['id']; ?>" class="btn">View Books</a>
                            <?php endif; ?>
                            <?php if ($author['book_count'] == 0): ?>
                                <a href="?delete=<?php echo $author['id']; ?>" 
                                   class="btn" 
                                   style="background-color: #dc3545;"
                                   onclick="return confirm('Are you sure you want to delete this author?')">
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

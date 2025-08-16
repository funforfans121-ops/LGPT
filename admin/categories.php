<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle category addition
if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    
    $sql = "INSERT INTO categories (name) VALUES ('$name')";
    if (mysqli_query($conn, $sql)) {
        $success = "Category added successfully!";
    } else {
        $error = "Error adding category: " . mysqli_error($conn);
    }
}

// Handle category deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if category is being used by any books
    $check = mysqli_query($conn, "SELECT id FROM books WHERE category_id = $id");
    if (mysqli_num_rows($check) > 0) {
        $error = "Cannot delete category: It is being used by one or more books";
    } else {
        mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
        $success = "Category deleted successfully!";
    }
}

// Get all categories
$categories = mysqli_query($conn, "SELECT c.*, COUNT(b.id) as book_count 
                                 FROM categories c 
                                 LEFT JOIN books b ON c.id = b.category_id 
                                 GROUP BY c.id 
                                 ORDER BY c.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Library Management System</title>
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
        <h1>Manage Categories</h1>
        
        <div class="card">
            <h2>Add New Category</h2>
            
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
                    <label for="name">Category Name:</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="add_category" class="btn">Add Category</button>
                </div>
            </form>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Existing Categories</h2>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Books in Category</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <td><?php echo $category['name']; ?></td>
                        <td><?php echo $category['book_count']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($category['created_at'])); ?></td>
                        <td>
                            <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="btn">Edit</a>
                            <?php if ($category['book_count'] > 0): ?>
                                <a href="category_books.php?id=<?php echo $category['id']; ?>" class="btn">View Books</a>
                            <?php endif; ?>
                            <?php if ($category['book_count'] == 0): ?>
                                <a href="?delete=<?php echo $category['id']; ?>" 
                                   class="btn" 
                                   style="background-color: #dc3545;"
                                   onclick="return confirm('Are you sure you want to delete this category?')">
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

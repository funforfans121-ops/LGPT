<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle rack addition
if (isset($_POST['add_rack'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    
    $sql = "INSERT INTO location_racks (name) VALUES ('$name')";
    if (mysqli_query($conn, $sql)) {
        $success = "Location rack added successfully!";
    } else {
        $error = "Error adding location rack: " . mysqli_error($conn);
    }
}

// Handle rack deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if rack has any books
    $check = mysqli_query($conn, "SELECT id FROM books WHERE rack_id = $id");
    if (mysqli_num_rows($check) > 0) {
        $error = "Cannot delete location rack: It contains books";
    } else {
        mysqli_query($conn, "DELETE FROM location_racks WHERE id = $id");
        $success = "Location rack deleted successfully!";
    }
}

// Get all racks with book counts
$racks = mysqli_query($conn, "SELECT r.*, COUNT(b.id) as book_count 
                             FROM location_racks r 
                             LEFT JOIN books b ON r.id = b.rack_id 
                             GROUP BY r.id 
                             ORDER BY r.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Location Racks - Library Management System</title>
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
        <h1>Manage Location Racks</h1>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <h2>Add New Location Rack</h2>
            
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
                    <label for="name">Rack Name/Location:</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           placeholder="e.g., Rack A-1, First Floor - Section B" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="add_rack" class="btn">Add Location Rack</button>
                </div>
            </form>
            </div>
            
            <div class="card">
                <h2>Rack Management Tips</h2>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <li>Use clear and consistent naming conventions for racks</li>
                    <li>Consider including floor/section information in rack names</li>
                    <li>Make sure rack locations are easily identifiable in the library</li>
                    <li>Regularly verify that books are in their assigned racks</li>
                    <li>Update rack locations when reorganizing the library layout</li>
                </ul>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Existing Location Racks</h2>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Rack Name/Location</th>
                        <th>Books in Rack</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($rack = mysqli_fetch_assoc($racks)): ?>
                    <tr>
                        <td><?php echo $rack['name']; ?></td>
                        <td><?php echo $rack['book_count']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($rack['created_at'])); ?></td>
                        <td>
                            <a href="edit_rack.php?id=<?php echo $rack['id']; ?>" class="btn">Edit</a>
                            <?php if ($rack['book_count'] > 0): ?>
                                <a href="rack_books.php?id=<?php echo $rack['id']; ?>" class="btn">View Books</a>
                            <?php endif; ?>
                            <?php if ($rack['book_count'] == 0): ?>
                                <a href="?delete=<?php echo $rack['id']; ?>" 
                                   class="btn" 
                                   style="background-color: #dc3545;"
                                   onclick="return confirm('Are you sure you want to delete this location rack?')">
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

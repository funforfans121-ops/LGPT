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

if (isset($_POST['edit_rack'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    
    if (mysqli_query($conn, "UPDATE location_racks SET name = '$name' WHERE id = $id")) {
        $success = "Location rack updated successfully!";
        $rack['name'] = $name;
    } else {
        $error = "Error updating location rack: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Location Rack - Library Management System</title>
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
                <li><a href="change_password.php">Change Password</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Edit Location Rack</h1>
        
        <div class="card" style="max-width: 500px; margin: 0 auto;">
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
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Rack Name/Location:</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           value="<?php echo $rack['name']; ?>" 
                           placeholder="e.g., Floor 1 - Section A, Rack 3" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="edit_rack" class="btn">Update Location Rack</button>
                    <a href="racks.php" class="btn" style="background-color: #6c757d;">Back</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

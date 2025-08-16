<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Store return URL in session if provided
if (isset($_GET['return_to'])) {
    $_SESSION['add_book_return_url'] = $_GET['return_to'];
} elseif (!isset($_SESSION['add_book_return_url']) && isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['add_book_return_url'] = $_SERVER['HTTP_REFERER'];
}

// Handle form submission
if (isset($_POST['submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    $author_id = (int)$_POST['author_id'];
    $category_id = (int)$_POST['category_id'];
    $rack_id = (int)$_POST['rack_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Validate input
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($isbn)) $errors[] = "ISBN is required";
    if ($author_id <= 0) $errors[] = "Please select an author";
    if ($category_id <= 0) $errors[] = "Please select a category";
    if ($rack_id <= 0) $errors[] = "Please select a location rack";
    if ($quantity <= 0) $errors[] = "Quantity must be greater than 0";
    
    // Check if ISBN is unique
    $check = mysqli_query($conn, "SELECT id FROM books WHERE isbn = '$isbn'");
    if (mysqli_num_rows($check) > 0) {
        $errors[] = "A book with this ISBN already exists";
    }
    
    if (empty($errors)) {
        // Insert the book
        $sql = "INSERT INTO books (title, isbn, author_id, category_id, rack_id, quantity, available_quantity) 
                VALUES ('$title', '$isbn', $author_id, $category_id, $rack_id, $quantity, $quantity)";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Book added successfully!";
            // Redirect to stored return URL or default to books.php
            $return_url = isset($_SESSION['add_book_return_url']) ? $_SESSION['add_book_return_url'] : 'books.php';
            unset($_SESSION['add_book_return_url']); // Clear the stored URL
            header("Location: " . $return_url);
            exit();
        } else {
            $error = "Error adding book: " . mysqli_error($conn);
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Get authors, categories, and racks for dropdowns
$authors = mysqli_query($conn, "SELECT * FROM authors ORDER BY name");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$racks = mysqli_query($conn, "SELECT * FROM location_racks ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book - Library Management System</title>
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
        <h1>Add New Book</h1>
        
        <div class="card" style="max-width: 600px; margin: 20px auto;">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" class="form-control" required 
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="isbn">ISBN:</label>
                    <input type="text" name="isbn" id="isbn" class="form-control" required
                           value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="author_id">Author:</label>
                    <select name="author_id" id="author_id" class="form-control" required>
                        <option value="">Select Author</option>
                        <?php while ($author = mysqli_fetch_assoc($authors)): ?>
                            <option value="<?php echo $author['id']; ?>" 
                                    <?php echo (isset($_POST['author_id']) && $_POST['author_id'] == $author['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($author['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $category['id']; ?>"
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="rack_id">Location Rack:</label>
                    <select name="rack_id" id="rack_id" class="form-control" required>
                        <option value="">Select Location Rack</option>
                        <?php while ($rack = mysqli_fetch_assoc($racks)): ?>
                            <option value="<?php echo $rack['id']; ?>"
                                    <?php echo (isset($_POST['rack_id']) && $_POST['rack_id'] == $rack['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rack['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" required min="1"
                           value="<?php echo isset($_POST['quantity']) ? (int)$_POST['quantity'] : '1'; ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" name="submit" class="btn">Add Book</button>
                    <a href="<?php echo isset($_SESSION['add_book_return_url']) ? htmlspecialchars($_SESSION['add_book_return_url']) : 'books.php'; ?>" class="btn" style="background-color: #6c757d;">Back</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

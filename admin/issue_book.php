<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['issue_book'])) {
    $user_id = (int)$_POST['user_id'];
    $book_id = (int)$_POST['book_id'];
    $issue_date = $_POST['issue_date'];
    $return_date = $_POST['return_date'];
    
    // Check if book is available
    $book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id = $book_id"));
    if ($book['available_quantity'] > 0) {
        // Create issue record
        $sql = "INSERT INTO book_issues (book_id, user_id, issue_date, return_date, status) 
                VALUES ($book_id, $user_id, '$issue_date', '$return_date', 'approved')";
        
        if (mysqli_query($conn, $sql)) {
            // Update book available quantity
            mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = $book_id");
            $success = "Book issued successfully!";
        } else {
            $error = "Error issuing book: " . mysqli_error($conn);
        }
    } else {
        $error = "Book is not available for issue";
    }
}

// Get all active users
$users = mysqli_query($conn, "SELECT * FROM users WHERE role = 'user' AND status = 'active' ORDER BY username");

// Get all books with available copies
$books = mysqli_query($conn, "SELECT * FROM books WHERE available_quantity > 0 ORDER BY title");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Book - Library Management System</title>
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
        <h1>Issue Book</h1>
        
        <div class="card">
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
                    <label for="user_id">Select User:</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">Select a user</option>
                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo $user['full_name']; ?> (<?php echo $user['username']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="book_id">Select Book:</label>
                    <select name="book_id" id="book_id" class="form-control" required>
                        <option value="">Select a book</option>
                        <?php while ($book = mysqli_fetch_assoc($books)): ?>
                            <option value="<?php echo $book['id']; ?>">
                                <?php echo $book['title']; ?> (Available: <?php echo $book['available_quantity']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="issue_date">Issue Date:</label>
                    <input type="date" name="issue_date" id="issue_date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="return_date">Return Date:</label>
                    <input type="date" name="return_date" id="return_date" class="form-control" 
                           value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="issue_book" class="btn">Issue Book</button>
                    <a href="issues.php" class="btn" style="background-color: #6c757d;">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Set minimum dates for issue and return date fields
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('issue_date').setAttribute('min', today);
            document.getElementById('return_date').setAttribute('min', today);
            
            // Update return date min when issue date changes
            document.getElementById('issue_date').addEventListener('change', function() {
                document.getElementById('return_date').setAttribute('min', this.value);
                if (document.getElementById('return_date').value < this.value) {
                    document.getElementById('return_date').value = this.value;
                }
            });
        });
    </script>
</body>
</html>

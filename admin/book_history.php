<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if book ID is provided
if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit();
}

$book_id = (int)$_GET['id'];

// Store the referrer URL in session if it's provided
if (isset($_GET['return_to'])) {
    $_SESSION['book_history_return_url'] = $_GET['return_to'];
} elseif (!isset($_SESSION['book_history_return_url']) && isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['book_history_return_url'] = $_SERVER['HTTP_REFERER'];
}

// Get book details
$book = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT b.*, c.name as category_name, a.name as author_name, r.name as rack_name 
     FROM books b 
     LEFT JOIN categories c ON b.category_id = c.id 
     LEFT JOIN authors a ON b.author_id = a.id 
     LEFT JOIN location_racks r ON b.rack_id = r.id 
     WHERE b.id = $book_id"
));

if (!$book) {
    header("Location: books.php");
    exit();
}

// Get book's issue history
$sql = "SELECT bi.*, u.username as user_name
        FROM book_issues bi
        JOIN users u ON bi.user_id = u.id
        WHERE bi.book_id = $book_id
        ORDER BY bi.created_at DESC";
$history = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book History - Library Management System</title>
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
        <h1>Book History</h1>
        
        <div class="card" style="margin-bottom: 20px;">
            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
            <table class="table">
                <tr>
                    <th>ISBN:</th>
                    <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                    <th>Category:</th>
                    <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                </tr>
                <tr>
                    <th>Author:</th>
                    <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                    <th>Location:</th>
                    <td><?php echo htmlspecialchars($book['rack_name']); ?></td>
                </tr>
                <tr>
                    <th>Total Quantity:</th>
                    <td><?php echo $book['quantity']; ?></td>
                    <th>Available:</th>
                    <td><?php echo $book['available_quantity']; ?></td>
                </tr>
            </table>
        </div>
        
        <div class="card">
            <h3>Issue History</h3>
            <?php if (mysqli_num_rows($history) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Actual Return Date</th>
                            <th>Status</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($history)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo $row['issue_date']; ?></td>
                            <td><?php echo $row['return_date']; ?></td>
                            <td><?php echo $row['actual_return_date'] ? $row['actual_return_date'] : '-'; ?></td>
                            <td>
                                <span style="color: 
                                    <?php
                                    switch($row['status']) {
                                        case 'pending': echo '#ffc107'; break;
                                        case 'approved': echo '#28a745'; break;
                                        case 'rejected': echo '#dc3545'; break;
                                        case 'returned': echo '#17a2b8'; break;
                                    }
                                    ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: <?php echo $row['fine'] > 0 ? '#28a745' : '#000000ff'; ?>">
                                    ₹<?php echo number_format($row['fine'], 2); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No history available for this book.</p>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <a href="<?php echo isset($_SESSION['book_history_return_url']) ? htmlspecialchars($_SESSION['book_history_return_url']) : 'books.php'; ?>" class="btn">Back</a>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['book_history_return_url']); // Clear the stored URL after use ?>
</body>
</html>

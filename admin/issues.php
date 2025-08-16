<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle request actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $issue_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $issue_date = date('d-m-Y');
        $return_date = date('d-m-Y', strtotime('+7 days'));
        
        $sql = "UPDATE book_issues SET 
                status = 'approved',
                issue_date = '$issue_date',
                return_date = '$return_date'
                WHERE id = $issue_id AND status = 'pending'";
        mysqli_query($conn, $sql);
        
        // Update book available quantity
        $book_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT book_id FROM book_issues WHERE id = $issue_id"))['book_id'];
        mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = $book_id");
    } else if ($action === 'reject') {
        mysqli_query($conn, "UPDATE book_issues SET status = 'rejected' WHERE id = $issue_id AND status = 'pending'");
    
    
    // Redirect back to issues page
    header("Location: issues.php");
    exit();
        $book_id = $issue['book_id'];
        mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity + 1 WHERE id = $book_id");
    }
    
    header("Location: issues.php");
    exit();
}

// Get all book issues
$sql = "SELECT bi.*, b.title, u.username, u.full_name,
        DATE_FORMAT(bi.created_at, '%d-%m-%Y') as formatted_created_at,
        DATE_FORMAT(bi.issue_date, '%d-%m-%Y') as formatted_issue_date,
        DATE_FORMAT(bi.return_date, '%d-%m-%Y') as formatted_return_date,
        DATE_FORMAT(bi.actual_return_date, '%d-%m-%Y') as formatted_actual_return_date
        FROM book_issues bi 
        JOIN books b ON bi.book_id = b.id 
        JOIN users u ON bi.user_id = u.id 
        ORDER BY 
            CASE 
                WHEN bi.status = 'pending' THEN 1
                WHEN bi.status = 'approved' THEN 2
                ELSE 3
            END,
            bi.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Issues - Library Management System</title>
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
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Book Issues and Requests</h1>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Return Date</th>
                        <th>Actual Return Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['full_name']; ?> (<?php echo $row['username']; ?>)</td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['formatted_issue_date'] ?: '-'; ?></td>
                        <td><?php echo $row['formatted_return_date'] ?: '-'; ?></td>
                        <td><?php echo $row['formatted_actual_return_date'] ?: '-'; ?></td>
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
                        <td style="color: <?php echo $row['fine'] > 0 ? 'green' : 'black'; ?>">
                            ₹<?php echo number_format($row['fine'], 2); ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn" style="background-color: #28a745;">Approve</a>
                                <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn" style="background-color: #dc3545;">Reject</a>
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

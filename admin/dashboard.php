<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/fine_calculator.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Get statistics
$stats = [
    'total_books' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM books"))['count'],
    'total_users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'user'"))['count'],
    'pending_requests' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM book_issues WHERE status = 'pending'"))['count'],
    'books_out' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM book_issues WHERE status = 'approved' AND actual_return_date IS NULL"))['count']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library Management System</title>
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
        <h1>Welcome, <?php echo $_SESSION['admin_name']; ?>!</h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
            <a href="books.php" class="card" style="text-decoration: none; color: inherit;">
                <h3>Total Books</h3>
                <p style="font-size: 2em; text-align: center;"><?php echo $stats['total_books']; ?></p>
                <small style="color: #666; text-align: center; display: block;">Click to manage books</small>
            </a>
            
            <a href="users.php" class="card" style="text-decoration: none; color: inherit;">
                <h3>Registered Users</h3>
                <p style="font-size: 2em; text-align: center;"><?php echo $stats['total_users']; ?></p>
                <small style="color: #666; text-align: center; display: block;">Click to manage users</small>
            </a>
            
            <a href="pending_requests.php" class="card" style="text-decoration: none; color: inherit;">
                <h3>Pending Requests</h3>
                <p style="font-size: 2em; text-align: center;"><?php echo $stats['pending_requests']; ?></p>
                <small style="color: #666; text-align: center; display: block;">Click to view all requests</small>
            </a>
            
            <a href="current_issues.php" class="card" style="text-decoration: none; color: inherit;">
                <h3>Books Currently Out</h3>
                <p style="font-size: 2em; text-align: center;"><?php echo $stats['books_out']; ?></p>
                <small style="color: #666; text-align: center; display: block;">Click to view current issues</small>
            </a>

            <?php
            // Update all fines to ensure they're current
            updateAllFines($conn);
            
            // Get the total fines using the centralized system
            $total_fines = mysqli_fetch_assoc(mysqli_query($conn, 
                "SELECT SUM(fine) as total_fines FROM book_issues 
                 WHERE fine > 0"
            ))['total_fines'] ?? 0;
            ?>
            <a href="fines.php" class="card" style="text-decoration: none; color: inherit;">
                <h3>Total Fines to Collect</h3>
                <p style="font-size: 2em; text-align: center; color: <?php echo $total_fines > 0 ? 'green' : 'black'; ?>">₹<?php echo number_format($total_fines, 2); ?></p>
                <small style="color: #666; text-align: center; display: block;">Click to view all fines</small>
            </a>
        </div>

        <?php if ($stats['pending_requests'] > 0): ?>
        <div class="card" style="margin-top: 20px;">
            <h2>Recent Book Requests</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Request Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT bi.*, b.title, u.username, u.full_name,
                           DATE_FORMAT(bi.created_at, '%d-%m-%Y') as formatted_created_at
                           FROM book_issues bi 
                           JOIN books b ON bi.book_id = b.id 
                           JOIN users u ON bi.user_id = u.id 
                           WHERE bi.status = 'pending' 
                           ORDER BY bi.created_at DESC 
                           LIMIT 5";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td><?php echo $row['full_name']; ?> (<?php echo $row['username']; ?>)</td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['formatted_created_at']; ?></td>
                        <td>
                            <a href="process_request.php?id=<?php echo $row['id']; ?>&action=approve" class="btn">Approve</a>
                            <a href="process_request.php?id=<?php echo $row['id']; ?>&action=reject" class="btn" style="background-color: #dc3545;">Reject</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div style="margin-top: 10px;">
                    <a href="pending_requests.php" class="btn">View All Requests</a>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($stats['books_out'] > 0): ?>
        <div class="card" style="margin-top: 20px;">
            <h2>Current Issues</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Fine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT bi.*, b.title, u.username, u.full_name,
                           DATE_FORMAT(bi.issue_date, '%d-%m-%Y') as formatted_issue_date,
                           DATE_FORMAT(bi.return_date, '%d-%m-%Y') as formatted_return_date,
                           CASE 
                               WHEN bi.return_date < CURDATE() THEN DATEDIFF(CURDATE(), bi.return_date) * 10
                               ELSE 0 
                           END as current_fine
                           FROM book_issues bi 
                           JOIN books b ON bi.book_id = b.id 
                           JOIN users u ON bi.user_id = u.id 
                           WHERE bi.status = 'approved' AND bi.actual_return_date IS NULL
                           ORDER BY bi.issue_date ASC
                           LIMIT 5";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td><?php echo $row['full_name']; ?> (<?php echo $row['username']; ?>)</td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['formatted_issue_date']; ?></td>
                        <td><?php echo $row['formatted_return_date']; ?></td>
                        <td>
                            <span style="color: <?php echo $row['current_fine'] > 0 ? '#28a745' : '#000000ff'; ?>">
                                ₹<?php echo number_format($row['current_fine'], 2); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div style="margin-top: 10px;">
                    <a href="current_issues.php" class="btn">View All Current Issues</a>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

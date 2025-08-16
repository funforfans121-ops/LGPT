<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Fines to Collect - Library Management System</title>
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
        <h1>Total Fines to Collect</h1>
        
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
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require_once '../includes/fine_calculator.php';
                    
                    // Update all fines to ensure they're current
                    updateAllFines($conn);
                    
                    $sql = "SELECT bi.*, b.title, u.username, u.full_name,
                           DATE_FORMAT(bi.issue_date, '%d-%m-%Y') as formatted_issue_date,
                           DATE_FORMAT(bi.return_date, '%d-%m-%Y') as formatted_return_date,
                           DATE_FORMAT(bi.actual_return_date, '%d-%m-%Y') as formatted_actual_return_date
                           FROM book_issues bi 
                           JOIN books b ON bi.book_id = b.id 
                           JOIN users u ON bi.user_id = u.id 
                           WHERE bi.fine > 0
                           ORDER BY bi.fine DESC";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td><?php echo $row['full_name']; ?> (<?php echo $row['username']; ?>)</td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['formatted_issue_date']; ?></td>
                        <td><?php echo $row['formatted_return_date']; ?></td>
                        <td><?php echo $row['formatted_actual_return_date'] ?: '-'; ?></td>
                        <td>
                            <span style="color: 
                                <?php
                                switch($row['status']) {
                                    case 'pending': echo '#ffc107'; break;
                                    case 'approved': echo '#28a745'; break;
                                    case 'rejected': echo '#dc3545'; break;
                                    case 'returned': echo '#17a2b8'; break;
                                    default: echo '#6c757d';
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
                    <?php endwhile; 
                    
                    // Calculate total fines directly from the database
                    $total_query = "SELECT SUM(fine) as total_fines FROM book_issues WHERE fine > 0";
                    $total_result = mysqli_query($conn, $total_query);
                    $total_fines = mysqli_fetch_assoc($total_result)['total_fines'] ?? 0;
                    ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; border-top: 2px solid #ddd;">
                        <td colspan="6" style="text-align: center;">Total Fines:</td>
                        <td style="color: <?php echo $total_fines > 0 ? '#28a745' : '#000000ff'; ?>">
                            ₹<?php echo number_format($total_fines, 2); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <?php if (mysqli_num_rows($result) === 0): ?>
                <p>No fines to collect.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

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
    <title>Current Issues - Library Management System</title>
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
        <h1>All Current Issues</h1>
        
        <div class="card">
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
                           WHERE bi.status = 'approved' 
                           AND bi.actual_return_date IS NULL
                           ORDER BY bi.issue_date DESC";
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
            <?php if (mysqli_num_rows($result) === 0): ?>
                <p>No current issues found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

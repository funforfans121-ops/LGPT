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
    <title>Pending Book Requests - Library Management System</title>
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
        <h1>All Pending Book Requests</h1>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Requested On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT bi.*, b.title, u.username, u.full_name,
                           DATE_FORMAT(bi.created_at, '%d-%m-%Y') as formatted_request_date
                           FROM book_issues bi 
                           JOIN books b ON bi.book_id = b.id 
                           JOIN users u ON bi.user_id = u.id 
                           WHERE bi.status = 'pending' 
                           ORDER BY bi.created_at DESC";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td><?php echo $row['full_name']; ?> (<?php echo $row['username']; ?>)</td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['formatted_request_date']; ?></td>
                        <td>
                            <a href="process_request.php?id=<?php echo $row['id']; ?>&action=approve" class="btn">Approve</a>
                            <a href="process_request.php?id=<?php echo $row['id']; ?>&action=reject" class="btn" style="background-color: #dc3545;">Reject</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if (mysqli_num_rows($result) === 0): ?>
                <p>No pending requests found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

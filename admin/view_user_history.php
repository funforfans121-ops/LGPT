<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/fine_calculator.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Get user details
$user_id = (int)$_GET['id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

if (!$user) {
    header("Location: users.php");
    exit();
}

// Get user's book history
$sql = "SELECT bi.*, b.title 
        FROM book_issues bi 
        JOIN books b ON bi.book_id = b.id 
        WHERE bi.user_id = $user_id 
        ORDER BY bi.created_at DESC";
$history = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User History - Library Management System</title>
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
        <h1>User History - <?php echo $user['full_name']; ?></h1>
        
        <div class="card">
            <h2>User Information</h2>
            <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
            <p><strong>Status:</strong> 
                <span style="color: <?php echo $user['status'] == 'active' ? '#28a745' : '#dc3545'; ?>">
                    <?php echo ucfirst($user['status']); ?>
                </span>
            </p>
            <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            <?php
            // Update all fines to ensure they're current
            updateAllFines($conn);
            
            // Get user's total fines
            $total_fines = getUserTotalFines($conn, $user_id);
            ?>
            <p>
                <strong>Total Outstanding Fines:</strong> 
                <span style="color: <?php echo $total_fines > 0 ? 'green' : 'black'; ?>">
                    ₹<?php echo number_format($total_fines, 2); ?>
                </span>
            </p>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Book Issue History</h2>
            
            <?php if (mysqli_num_rows($history) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Actual Return Date</th>
                            <th>Status</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $running_total = 0; ?>
                        <?php while ($row = mysqli_fetch_assoc($history)): ?>
                        <?php $running_total += $row['fine']; ?>
                        <tr>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $row['issue_date']; ?></td>
                            <td><?php echo $row['return_date']; ?></td>
                            <td><?php echo $row['actual_return_date'] ?: '-'; ?></td>
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
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align: right;"><strong>Total Fines:</strong></td>
                            <td style="color: <?php echo $running_total > 0 ? '#28a745' : '#000000ff'; ?>">
                                <strong>₹<?php echo number_format($running_total, 2); ?></strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p>No history found for this user.</p>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <a href="users.php" class="btn" style="background-color: #6c757d;">Back</a>
            </div>
        </div>
    </div>
</body>
</html>

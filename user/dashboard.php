<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user's current issues
$sql = "SELECT bi.*, b.title, b.isbn,
        DATE_FORMAT(bi.created_at, '%d-%m-%Y') as formatted_request_date,
        DATE_FORMAT(bi.issue_date, '%d-%m-%Y') as formatted_issue_date,
        DATE_FORMAT(bi.return_date, '%d-%m-%Y') as formatted_return_date
        FROM book_issues bi 
        JOIN books b ON bi.book_id = b.id 
        WHERE bi.user_id = {$_SESSION['user_id']} 
        AND bi.status IN ('approved', 'pending')
        ORDER BY bi.created_at DESC";
$current_issues = mysqli_query($conn, $sql);

// Get user's issue history
$sql = "SELECT bi.*, b.title 
        FROM book_issues bi 
        JOIN books b ON bi.book_id = b.id 
        WHERE bi.user_id = {$_SESSION['user_id']} 
        AND bi.status IN ('returned', 'rejected')
        ORDER BY bi.created_at DESC 
        LIMIT 10";
$history = mysqli_query($conn, $sql);

// Get total fines
$total_fines = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT SUM(fine) as total FROM book_issues 
     WHERE user_id = {$_SESSION['user_id']}")
)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Library Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="container">
        <h1>Welcome to Your Library Dashboard</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php
        // Get statistics
        $current_borrowed = mysqli_num_rows(mysqli_query($conn, 
            "SELECT id FROM book_issues 
             WHERE user_id = {$_SESSION['user_id']} 
             AND status = 'approved'"
        ));
        
        $pending_requests = mysqli_num_rows(mysqli_query($conn, 
            "SELECT id FROM book_issues 
             WHERE user_id = {$_SESSION['user_id']} 
             AND status = 'pending'"
        ));
        
        $total_returned = mysqli_num_rows(mysqli_query($conn, 
            "SELECT id FROM book_issues 
             WHERE user_id = {$_SESSION['user_id']} 
             AND status = 'returned'"
        ));
        ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <a href="history.php?filter=current" class="card" style="text-align: center; padding: 20px; text-decoration: none; color: inherit;">
                <h3>Currently Borrowed</h3>
                <p style="font-size: 2em; margin: 10px 0; color: #28a745;"><?php echo $current_borrowed; ?></p>
                <small style="color: #666;">Click to view details</small>
            </a>
            
            <a href="history.php?filter=pending" class="card" style="text-align: center; padding: 20px; text-decoration: none; color: inherit;">
                <h3>Pending Requests</h3>
                <p style="font-size: 2em; margin: 10px 0; color: #ffc107;"><?php echo $pending_requests; ?></p>
                <small style="color: #666;">Click to view details</small>
            </a>
            
            <a href="history.php?filter=returned" class="card" style="text-align: center; padding: 20px; text-decoration: none; color: inherit;">
                <h3>Total Books Returned</h3>
                <p style="font-size: 2em; margin: 10px 0; color: #17a2b8;"><?php echo $total_returned; ?></p>
                <small style="color: #666;">Click to view details</small>
            </a>
            
            <a href="history.php?filter=fines" class="card" style="text-align: center; padding: 20px; text-decoration: none; color: inherit;">
                <h3>Total Fines</h3>
                <p style="font-size: 2em; margin: 10px 0; color: <?php echo $total_fines > 0 ? '#dc3545' : '#28a745'; ?>">
                    ₹<?php echo number_format($total_fines, 2); ?>
                </p>
                <small style="color: #666;"><?php echo $total_fines > 0 ? 'Click to view details' : 'No fines due'; ?></small>
            </a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
            <div class="card">
                <h3>Current Issues/Requests</h3>
                <?php if (mysqli_num_rows($current_issues) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Requested On</th>
                                <th>Issue Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($issue = mysqli_fetch_assoc($current_issues)): ?>
                            <tr>
                                <td><?php echo $issue['title']; ?></td>
                                <td><?php echo $issue['formatted_request_date']; ?></td>
                                <td><?php echo $issue['formatted_issue_date'] ?: '-'; ?></td>
                                <td><?php echo $issue['formatted_return_date'] ?: '-'; ?></td>
                                <td>
                                    <span style="color: <?php echo $issue['status'] == 'approved' ? '#28a745' : '#ffc107'; ?>">
                                        <?php echo ucfirst($issue['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($issue['status'] === 'approved'): ?>
                                        <a href="return_book.php?id=<?php echo $issue['id']; ?>&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" 
                                           class="btn"
                                           onclick="return confirm('Are you sure you want to return this book?')">
                                            Return Book
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No current issues or requests.</p>
                <?php endif; ?>
            </div>
            
        </div>
        
        <div class="card">
            <h3>Recent History</h3>
            <?php 
            // Get recent history (all types)
            $recent_sql = "SELECT bi.*, b.title,
                    DATE_FORMAT(bi.created_at, '%d-%m-%Y') as formatted_request_date,
                    DATE_FORMAT(bi.issue_date, '%d-%m-%Y') as formatted_issue_date,
                    DATE_FORMAT(bi.return_date, '%d-%m-%Y') as formatted_return_date
                    FROM book_issues bi 
                    JOIN books b ON bi.book_id = b.id 
                    WHERE bi.user_id = {$_SESSION['user_id']} 
                    ORDER BY bi.created_at DESC 
                    LIMIT 10";
            $recent_history = mysqli_query($conn, $recent_sql);
            if (mysqli_num_rows($recent_history) > 0): 
            ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Requested On</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Fine</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($recent_history)): ?>
                        <tr>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $row['formatted_request_date']; ?></td>
                            <td><?php echo $row['formatted_issue_date'] ?: '-'; ?></td>
                            <td><?php echo $row['formatted_return_date'] ?: '-'; ?></td>
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
                                <?php if ($row['status'] === 'returned'): ?>
                                    <span style="color: <?php echo $row['fine'] > 0 ? '#dc3545' : '#000000ff'; ?>">
                                        ₹<?php echo number_format($row['fine'], 2); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'approved' && !$row['actual_return_date']): ?>
                                    <a href="return_book.php?id=<?php echo $row['id']; ?>&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" 
                                       class="btn"
                                       onclick="return confirm('Are you sure you want to return this book?')">
                                        Return Book
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="history.php" class="btn" style="margin-top: 10px;">View Full History</a>
            <?php else: ?>
                <p>No history available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

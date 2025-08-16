<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user's book history
$user_id = $_SESSION['user_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$where = "bi.user_id = $user_id";
switch ($filter) {
    case 'current':
        $where .= " AND bi.status = 'approved'";
        break;
    case 'pending':
        $where .= " AND bi.status = 'pending'";
        break;
    case 'returned':
        $where .= " AND bi.status = 'returned'";
        break;
    case 'fines':
        $where .= " AND bi.fine > 0";
        break;
}

$sql = "SELECT bi.*, b.title,
        DATE_FORMAT(bi.created_at, '%d-%m-%Y') as requested_on,
        DATE_FORMAT(bi.issue_date, '%d-%m-%Y') as formatted_issue_date,
        DATE_FORMAT(bi.return_date, '%d-%m-%Y') as formatted_return_date,
        DATE_FORMAT(bi.actual_return_date, '%d-%m-%Y') as formatted_actual_return_date
        FROM book_issues bi 
        JOIN books b ON bi.book_id = b.id 
        WHERE $where 
        ORDER BY bi.created_at DESC";
$history = mysqli_query($conn, $sql);

// Calculate total fines
$total_fines = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT SUM(fine) as total FROM book_issues WHERE user_id = $user_id"
))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My History - Library Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="container">
        <?php
        $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
        $title = 'My Library History';
        
        switch ($filter) {
            case 'current':
                $title = 'Currently Borrowed Books';
                break;
            case 'pending':
                $title = 'Pending Book Requests';
                break;
            case 'returned':
                $title = 'Total Returned Books';
                break;
            case 'fines':
                $title = 'Total Fines';
                $where .= " AND status = 'returned' AND fine > 0";
                break;
        }
        ?>
        <h1><?php echo $title; ?></h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if ($total_fines > 0 && $filter === 'fines'): ?>
            <div class="alert alert-warning" style="color: #dc3545; background-color: #f8d7da; border-color: #f5c6cb;">
                <strong>You have outstanding fines of ₹<?php echo number_format($total_fines, 2); ?>.</strong>
                Please pay your fines at the library.
            </div>
        <?php endif; ?>
        
        <div class="card">
            <?php if (mysqli_num_rows($history) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Requested On</th>
                            <?php if ($filter !== 'pending'): ?>
                                <th>Issue Date</th>
                                <th>Return Date</th>
                                <?php if ($filter === '' || $filter === 'returned' || $filter === 'fines'): ?>
                                    <th>Actual Return Date</th>
                                <?php endif; ?>
                            <?php endif; ?>
                            <th>Status</th>
                            <?php if ($filter !== 'pending'): ?>
                                <th>Fine</th>
                            <?php endif; ?>
                            <?php if ($filter === '' || $filter === 'current'): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($history)): ?>
                        <tr>
                            <td><?php echo $row['title']; ?></td>
                            <td><?php echo $row['requested_on']; ?></td>
                            <?php if ($filter !== 'pending'): ?>
                                <td><?php 
                                    echo ($row['status'] === 'pending' || $row['status'] === 'rejected') 
                                        ? '-' 
                                        : $row['formatted_issue_date']; 
                                ?></td>
                                <td><?php 
                                    echo ($row['status'] === 'pending' || $row['status'] === 'rejected') 
                                        ? '-' 
                                        : $row['formatted_return_date']; 
                                ?></td>
                                <?php if ($filter === '' || $filter === 'returned' || $filter === 'fines'): ?>
                                    <td><?php echo $row['formatted_actual_return_date'] ?: '-'; ?></td>
                                <?php endif; ?>
                            <?php endif; ?>
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
                            <?php if ($filter !== 'pending'): ?>
                                <td>
                                    <?php if ($row['status'] === 'returned'): ?>
                                        <span style="color: <?php echo $row['fine'] > 0 ? '#dc3545' : '#000'; ?>">
                                            ₹<?php echo number_format($row['fine'], 2); ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <?php if ($filter === '' || $filter === 'current'): ?>
                                <td>
                                    <?php if ($row['status'] === 'approved' && !$row['actual_return_date']): ?>
                                        <a href="return_book.php?id=<?php echo $row['id']; ?>&redirect=<?php echo urlencode($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']); ?>" 
                                           class="btn"
                                           onclick="return confirm('Are you sure you want to return this book?')">
                                            Return Book
                                        </a>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No history available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/fine_calculator.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle user status toggle
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $current_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM users WHERE id = $user_id"))['status'];
    $new_status = $current_status == 'active' ? 'disabled' : 'active';
    
    mysqli_query($conn, "UPDATE users SET status = '$new_status' WHERE id = $user_id AND role = 'user'");
    header("Location: users.php");
    exit();
}

// Update all fines first
updateAllFines($conn);

// Get all users with their total fines
$sql = "SELECT u.*, DATE_FORMAT(u.created_at, '%d-%m-%Y') as formatted_created_at,
        COALESCE((
            SELECT SUM(fine)
            FROM book_issues
            WHERE user_id = u.id AND fine > 0
        ), 0) as total_fines
        FROM users u
        WHERE u.role = 'user'
        ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Library Management System</title>
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
        <h1>Manage Users</h1>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Registered Date</th>
                        <th>Outstanding Fines</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td>
                            <span style="color: <?php echo $row['status'] == 'active' ? 'green' : 'red'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $row['formatted_created_at']; ?></td>
                        <td style="color: <?php echo $row['total_fines'] > 0 ? 'green' : 'black'; ?>">
                            ₹<?php echo number_format($row['total_fines'], 2); ?>
                        </td>
                        <td>
                            <a href="?toggle_status&id=<?php echo $row['id']; ?>" 
                               class="btn" 
                               style="background-color: <?php echo $row['status'] == 'active' ? '#dc3545' : '#28a745'; ?>">
                                <?php echo $row['status'] == 'active' ? 'Disable' : 'Enable'; ?>
                            </a>
                            <a href="view_user_history.php?id=<?php echo $row['id']; ?>" class="btn">View History</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

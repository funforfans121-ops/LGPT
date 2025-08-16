<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $issue_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        // Set issue date as current date and return date as 7 days from now
        $issue_date = date('Y-m-d');
        $return_date = date('Y-m-d', strtotime('+7 days'));
        
        mysqli_query($conn, "UPDATE book_issues 
                           SET status = 'approved',
                               issue_date = '$issue_date',
                               return_date = '$return_date'
                           WHERE id = $issue_id AND status = 'pending'");
        
        // Update book available quantity
        $book_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT book_id FROM book_issues WHERE id = $issue_id"))['book_id'];
        mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = $book_id");
    } else if ($action === 'reject') {
        mysqli_query($conn, "UPDATE book_issues SET status = 'rejected' WHERE id = $issue_id AND status = 'pending'");
    }
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit();

<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $issue_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify this issue belongs to the current user and is in approved status
    $issue = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT * FROM book_issues 
         WHERE id = $issue_id 
         AND user_id = $user_id 
         AND status = 'approved' 
         AND actual_return_date IS NULL"
    ));
    
    if ($issue) {
        // Calculate fine if returned late
        $return_date = new DateTime($issue['return_date']);
        $today = new DateTime();
        $fine = 0;
        
        if ($today > $return_date) {
            $days_late = $today->diff($return_date)->days;
            $fine = $days_late * 10.00; // ₹10 per day late
        }
        
        // Update issue record
        mysqli_query($conn, "UPDATE book_issues SET 
            status = 'returned', 
            actual_return_date = CURRENT_DATE,
            fine = $fine 
            WHERE id = $issue_id"
        );
        
        // Update book available quantity
        $book_id = $issue['book_id'];
        mysqli_query($conn, "UPDATE books 
                           SET available_quantity = available_quantity + 1 
                           WHERE id = $book_id");
        
        $_SESSION['success'] = "Book returned successfully!" . ($fine > 0 ? " Fine charged: ₹$fine" : "");
        
        // Redirect back to the referring page or history.php as fallback
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'history.php';
        header("Location: $redirect");
        exit();
    }
}

// Redirect back to history page if no valid issue id
header("Location: history.php");
exit();

<?php
function updateAllFines($conn) {
    // Get all relevant book issues
    $sql = "SELECT id, return_date, actual_return_date, status 
            FROM book_issues 
            WHERE (status = 'approved' AND actual_return_date IS NULL)
               OR (status = 'returned' AND fine > 0)";
    
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $fine = 0;
        
        if ($row['status'] === 'approved') {
            // Book is still out
            $return_date = new DateTime($row['return_date']);
            $current_date = new DateTime();
            
            if ($current_date > $return_date) {
                $days_late = $current_date->diff($return_date)->days;
                $fine = $days_late * 10;
            }
        } else if ($row['status'] === 'returned' && $row['actual_return_date']) {
            // Book has been returned
            $return_date = new DateTime($row['return_date']);
            $actual_return_date = new DateTime($row['actual_return_date']);
            
            if ($actual_return_date > $return_date) {
                $days_late = $actual_return_date->diff($return_date)->days;
                $fine = $days_late * 10;
            }
        }
        
        // Update the fine in database
        mysqli_query($conn, "UPDATE book_issues SET fine = $fine WHERE id = {$row['id']}");
    }
}

// Function to get total fines for a user
function getUserTotalFines($conn, $user_id) {
    $sql = "SELECT COALESCE(SUM(fine), 0) as total_fines 
            FROM book_issues 
            WHERE user_id = $user_id 
            AND ((status = 'approved' AND actual_return_date IS NULL) 
                 OR (status = 'returned' AND fine > 0))";
    
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result)['total_fines'];
}

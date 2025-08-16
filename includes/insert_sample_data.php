<?php
require_once 'config.php';

// Function to check if data exists
function tableIsEmpty($conn, $table) {
    $result = mysqli_query($conn, "SELECT id FROM $table LIMIT 1");
    return mysqli_num_rows($result) == 0;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert Categories
    if (tableIsEmpty($conn, 'categories')) {
        $categories = [
            "Fiction",
            "Non-Fiction",
            "Science",
            "Technology",
            "History",
            "Literature",
            "Mathematics",
            "Computer Science"
        ];
        
        foreach ($categories as $category) {
            mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$category')");
        }
        echo "Categories added successfully.\n";
    }

    // Insert Authors
    if (tableIsEmpty($conn, 'authors')) {
        $authors = [
            "J.K. Rowling",
            "George R.R. Martin",
            "Stephen Hawking",
            "Robert C. Martin",
            "Thomas H. Cormen",
            "William Shakespeare",
            "Charles Dickens",
            "Jane Austen"
        ];
        
        foreach ($authors as $author) {
            mysqli_query($conn, "INSERT INTO authors (name) VALUES ('$author')");
        }
        echo "Authors added successfully.\n";
    }

    // Insert Location Racks
    if (tableIsEmpty($conn, 'location_racks')) {
        $racks = [
            "Rack-A1",
            "Rack-A2",
            "Rack-B1",
            "Rack-B2",
            "Rack-C1",
            "Rack-C2"
        ];
        
        foreach ($racks as $rack) {
            mysqli_query($conn, "INSERT INTO location_racks (name) VALUES ('$rack')");
        }
        echo "Location Racks added successfully.\n";
    }

    // Insert Books
    if (tableIsEmpty($conn, 'books')) {
        $books = [
            [
                'title' => 'Harry Potter and the Philosopher\'s Stone',
                'category' => 'Fiction',
                'author' => 'J.K. Rowling',
                'rack' => 'Rack-A1',
                'isbn' => '9780747532743',
                'quantity' => 5
            ],
            [
                'title' => 'A Brief History of Time',
                'category' => 'Science',
                'author' => 'Stephen Hawking',
                'rack' => 'Rack-B1',
                'isbn' => '9780553380163',
                'quantity' => 3
            ],
            [
                'title' => 'Clean Code',
                'category' => 'Technology',
                'author' => 'Robert C. Martin',
                'rack' => 'Rack-C1',
                'isbn' => '9780132350884',
                'quantity' => 4
            ],
            [
                'title' => 'Pride and Prejudice',
                'category' => 'Literature',
                'author' => 'Jane Austen',
                'rack' => 'Rack-A2',
                'isbn' => '9780141439518',
                'quantity' => 3
            ],
            [
                'title' => 'Introduction to Algorithms',
                'category' => 'Computer Science',
                'author' => 'Thomas H. Cormen',
                'rack' => 'Rack-C2',
                'isbn' => '9780262033848',
                'quantity' => 2
            ]
        ];

        foreach ($books as $book) {
            $category_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM categories WHERE name = '{$book['category']}'"))['id'];
            $author_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM authors WHERE name = '{$book['author']}'"))['id'];
            $rack_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM location_racks WHERE name = '{$book['rack']}'"))['id'];
            
            $safe_title = mysqli_real_escape_string($conn, $book['title']);
            $safe_isbn = mysqli_real_escape_string($conn, $book['isbn']);
            mysqli_query($conn, "INSERT INTO books (title, category_id, author_id, rack_id, isbn, quantity, available_quantity) 
                               VALUES ('$safe_title', $category_id, $author_id, $rack_id, '$safe_isbn', {$book['quantity']}, {$book['quantity']})");
        }
        echo "Books added successfully.\n";
    }

    // Insert Sample Users
    if (tableIsEmpty($conn, 'users') || mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role = 'user'")) == 0) {
        $password = md5("password123");
        
        $users = [
            [
                'username' => 'user1',
                'email' => 'user1@example.com',
                'full_name' => 'John Smith',
                'status' => 'active'  // Regular active user
            ],
            [
                'username' => 'user2',
                'email' => 'user2@example.com',
                'full_name' => 'Mary Johnson',
                'status' => 'active'  // User with multiple books issued
            ],
            [
                'username' => 'user3',
                'email' => 'user3@example.com',
                'full_name' => 'Robert Wilson',
                'status' => 'disabled'  // disabled user with pending fines
            ],
            [
                'username' => 'user4',
                'email' => 'user4@example.com',
                'full_name' => 'Sarah Davis',
                'status' => 'active'  // User with returned books history
            ],
            [
                'username' => 'user5',
                'email' => 'user5@example.com',
                'full_name' => 'Michael Brown',
                'status' => 'active'  // User with pending book requests
            ]
        ];

        foreach ($users as $user) {
            mysqli_query($conn, "INSERT INTO users (username, password, email, full_name, role, status) 
                               VALUES ('{$user['username']}', '$password', '{$user['email']}', '{$user['full_name']}', 'user', '{$user['status']}')");
        }
        echo "Sample users added successfully.\n";
    }

    // Add Sample Book Issues
    if (tableIsEmpty($conn, 'book_issues')) {
        $book_ids = array_map(function($row) { return $row['id']; }, 
                            mysqli_fetch_all(mysqli_query($conn, "SELECT id FROM books"), MYSQLI_ASSOC));
        
        if (!empty($book_ids)) {
            // User1: One pending request
            $user1_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username = 'user1'"))['id'];
            mysqli_query($conn, "INSERT INTO book_issues (book_id, user_id, status, created_at) 
                               VALUES ({$book_ids[0]}, $user1_id, 'pending', NOW())");

            // User2: Two current issues
            $user2_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username = 'user2'"))['id'];
            $issue_date = date('Y-m-d');
            $return_date = date('Y-m-d', strtotime('+7 days'));
            mysqli_query($conn, "INSERT INTO book_issues (book_id, user_id, status, issue_date, return_date, created_at) 
                               VALUES ({$book_ids[1]}, $user2_id, 'approved', '$issue_date', '$return_date', NOW())");
            mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = {$book_ids[1]}");
            mysqli_query($conn, "INSERT INTO book_issues (book_id, user_id, status, issue_date, return_date, created_at) 
                               VALUES ({$book_ids[2]}, $user2_id, 'approved', '$issue_date', '$return_date', NOW())");
            mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = {$book_ids[2]}");

            // User3: One overdue book with pending fine
            $user3_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username = 'user3'"))['id'];
            $past_issue_date = date('Y-m-d', strtotime('-20 days'));
            $past_return_date = date('Y-m-d', strtotime('-6 days'));
            mysqli_query($conn, "INSERT INTO book_issues (book_id, user_id, status, issue_date, return_date, fine, created_at) 
                               VALUES ({$book_ids[3]}, $user3_id, 'approved', '$past_issue_date', '$past_return_date', 30.00, NOW())");
            mysqli_query($conn, "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = {$book_ids[3]}");

            // User4: Two returned books (one with fine, one without)
            $user4_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username = 'user4'"))['id'];
            // Returned on time
            $past_issue_date1 = date('Y-m-d', strtotime('-14 days'));
            $past_return_date1 = date('Y-m-d', strtotime('-7 days'));
            $actual_return_date1 = date('Y-m-d', strtotime('-7 days'));
            mysqli_query($conn, "INSERT INTO book_issues (book_id, user_id, status, issue_date, return_date, actual_return_date, created_at) 
                               VALUES ({$book_ids[0]}, $user4_id, 'returned', '$past_issue_date1', '$past_return_date1', '$actual_return_date1', NOW())");
            // Returned late with fine
            $past_issue_date2 = date('Y-m-d', strtotime('-10 days'));
            $past_return_date2 = date('Y-m-d', strtotime('-3 days'));
            $actual_return_date2 = date('Y-m-d', strtotime('-1 days'));
            mysqli_query($conn, "INSERT INTO book_issues (book_id, user_id, status, issue_date, return_date, actual_return_date, fine, created_at) 
                               VALUES ({$book_ids[1]}, $user4_id, 'returned', '$past_issue_date2', '$past_return_date2', '$actual_return_date2', 20.00, NOW())");

            // User5: Multiple pending requests
            $user5_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username = 'user5'"))['id'];
            mysqli_query($conn, "INSERT INTO book_issues (book_id, user_id, status, created_at) 
                               VALUES ({$book_ids[2]}, $user5_id, 'pending', NOW())");
            mysqli_query($conn, "INSERT INTO book_issues (book_id, user_id, status, created_at) 
                               VALUES ({$book_ids[3]}, $user5_id, 'pending', NOW())");
        }
        echo "Sample book issues added successfully.\n";
    }

    // Commit transaction
    mysqli_commit($conn);
    echo "<script>
        alert('All sample data has been successfully inserted into the database!');
        window.location.href = '../index.php';
    </script>";
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo "<script>
        alert('Error: " . addslashes($e->getMessage()) . "');
        window.location.href = '../index.php';
    </script>";
}

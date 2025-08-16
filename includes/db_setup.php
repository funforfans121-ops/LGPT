<?php
// Database connection parameters
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'library_db');

function setupDatabase() {
    // First try to connect to MySQL server
    $conn = @mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => "Failed to connect to MySQL server. Error: " . mysqli_connect_error()
        ];
    }

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (!mysqli_query($conn, $sql)) {
        return [
            'success' => false,
            'message' => "Error creating database: " . mysqli_error($conn)
        ];
    }

    // Select the database
    if (!mysqli_select_db($conn, DB_NAME)) {
        return [
            'success' => false,
            'message' => "Error selecting database: " . mysqli_error($conn)
        ];
    }

    // Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'user') NOT NULL,
            status ENUM('active', 'disabled') DEFAULT 'active',
            profile_image VARCHAR(255) DEFAULT 'default.jpg',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS authors (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS location_racks (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS books (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            category_id INT,
            author_id INT,
            rack_id INT,
            isbn VARCHAR(13),
            quantity INT DEFAULT 1,
            available_quantity INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id),
            FOREIGN KEY (author_id) REFERENCES authors(id),
            FOREIGN KEY (rack_id) REFERENCES location_racks(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS book_issues (
            id INT PRIMARY KEY AUTO_INCREMENT,
            book_id INT,
            user_id INT,
            requested_on DATE DEFAULT NULL,
            issue_date DATE DEFAULT NULL,
            return_date DATE DEFAULT NULL,
            actual_return_date DATE DEFAULT NULL,
            fine DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('pending', 'approved', 'rejected', 'returned') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (book_id) REFERENCES books(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )"
    ];

    foreach ($tables as $sql) {
        if (!mysqli_query($conn, $sql)) {
            return [
                'success' => false,
                'message' => "Error creating table: " . mysqli_error($conn)
            ];
        }
    }

    // Insert default admin user if not exists
    $default_admin_password = md5("admin123");
    $check_admin = "SELECT id FROM users WHERE username = 'admin'";
    $result = mysqli_query($conn, $check_admin);

    if (mysqli_num_rows($result) == 0) {
        $admin_sql = "INSERT INTO users (username, password, email, full_name, role) 
                     VALUES ('admin', '$default_admin_password', 'admin@example.com', 'System Administrator', 'admin')";
        if (!mysqli_query($conn, $admin_sql)) {
            return [
                'success' => false,
                'message' => "Error creating admin user: " . mysqli_error($conn)
            ];
        }
    }

    return [
        'success' => true,
        'message' => "Database setup completed successfully!"
    ];
}

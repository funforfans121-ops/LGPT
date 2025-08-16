<?php
require_once __DIR__ . '/db_setup.php';

// First try connecting without database to check if MySQL is available
$base_conn = @mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
if (!$base_conn) {
    die("Could not connect to MySQL server: " . mysqli_connect_error());
}
mysqli_close($base_conn);

// Now try connecting to our database
$conn = @mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// If database connection fails, redirect to setup
if (!$conn) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'index.php') {
        header("Location: /LGPT/index.php?error=" . urlencode(mysqli_connect_error()));
        exit();
    }
    die("Database connection failed: " . mysqli_connect_error());
}

<?php
require_once 'includes/db_setup.php';

// Try to connect and set up the database if needed
$setup_result = setupDatabase();
if (!$setup_result['success']) {
    die("Database setup failed: " . $setup_result['message']);
}

// Now that we know the database exists, connect to it
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero {
            background-color: #1a1a1a;
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .feature-card {
            padding: 20px;
            border-radius: 5px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 40px 0;
        }
        
        .cta-btn {
            padding: 15px 30px;
            font-size: 1.1em;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .cta-btn.primary {
            background-color: #28a745;
            color: white;
        }
        
        .cta-btn.secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .cta-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container">
            <h1>Welcome to Library Management System</h1>
            <p>Your gateway to knowledge and endless possibilities</p>
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" style="margin-top: 20px;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" style="margin-top: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="cta-buttons">
            <a href="admin/" class="cta-btn primary">Admin Login</a>
            <a href="login.php" class="cta-btn primary">User Login</a>
            <a href="register.php" class="cta-btn secondary">New User Registration</a>
        </div>

        <div class="features">
            <div class="feature-card">
                <h3>Easy Book Search</h3>
                <p>Find your favorite books quickly with our advanced search system</p>
            </div>
            
            <div class="feature-card">
                <h3>Online Book Request</h3>
                <p>Request books online and get notifications about your requests</p>
            </div>
            
            <div class="feature-card">
                <h3>Digital Library Card</h3>
                <p>Manage your library account and track your reading history</p>
            </div>
        </div>

        <div class="card">
            <h2>About Our Library</h2>
            <p>Our library management system provides a seamless experience for both administrators and users. With features like online book requests, digital tracking, and efficient management tools, we make it easier for everyone to access and manage library resources.</p>
        </div>

        <div class="card">
            <h2>Library Rules</h2>
            <ul>
                <li>Maximum 3 books can be issued to a user at a time</li>
                <li>Books are issued for 14 days</li>
                <li>Fine of ₹1 per day for late returns</li>
                <li>Lost or damaged books must be replaced or paid for</li>
                <li>Maintain silence in the library premises</li>
            </ul>
        </div>

        <div class="card">
            <h2>Contact Us</h2>
            <p>For any queries or assistance, please contact us:</p>
            <p>Email: library@example.com</p>
            <p>Phone: +91 1234567890</p>
            <p>Address: Your Library Address, City, State - PIN</p>
        </div>
    </div>

    <footer style="background: #333; color: white; text-align: center; padding: 20px 0; margin-top: 40px;">
        <div class="container">
            <p>&copy; 2025 Library Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

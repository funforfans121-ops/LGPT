<?php
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$success_message = '';
$error_message = '';

// Get admin details
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $admin_id"));

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (password_verify($current_password, $admin['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                if (mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE id = $admin_id")) {
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Error changing password. Please try again.";
                }
            } else {
                $error_message = "New password must be at least 6 characters long.";
            }
        } else {
            $error_message = "New passwords do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }
}

// Handle profile image reset
if (isset($_POST['reset_image'])) {
    // Only delete current image if it's not already default.jpg
    if ($admin['profile_image'] !== 'default.jpg') {
        $old_image = '../uploads/profile_images/' . $admin['profile_image'];
        if (file_exists($old_image)) {
            unlink($old_image);
        }
        mysqli_query($conn, "UPDATE users SET profile_image = 'default.jpg' WHERE id = $admin_id");
        $admin['profile_image'] = 'default.jpg';
        $success_message = "Profile picture reset to default!";
    }
}

// Handle profile image upload
if (isset($_POST['upload_image'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES['profile_image']['size'];
        
        // Check file size (2MB limit)
        if ($filesize > 2 * 1024 * 1024) {
            $error_message = "File size must be less than 2MB.";
        } else if (in_array($filetype, $allowed)) {
            // Create unique filename
            $new_filename = 'admin_' . $admin_id . '_' . time() . '.' . $filetype;
            $upload_path = '../uploads/profile_images/' . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists and is not the default
                if ($admin['profile_image'] !== 'default.jpg') {
                    $old_image = '../uploads/profile_images/' . $admin['profile_image'];
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
                
                // Update database
                mysqli_query($conn, "UPDATE users SET profile_image = '$new_filename' WHERE id = $admin_id");
                $admin['profile_image'] = $new_filename; // Update current page display
                $success_message = "Profile image updated successfully!";
            } else {
                $error_message = "Error uploading image. Please try again.";
            }
        } else {
            $error_message = "Invalid file type. Please upload a jpg, jpeg, png, or gif file.";
        }
    } else {
        $error_message = "Please select an image to upload.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Library Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <?php include '../includes/alert_handler.php'; ?>
    <style>
        .profile-section {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .profile-image-container {
            text-align: center;
        }
        .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .profile-details {
            flex: 1;
        }
        .upload-form {
            margin-top: 1rem;
        }
        .section-divider {
            border-top: 1px solid #ddd;
            margin: 2rem 0;
            padding-top: 2rem;
        }
    </style>
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
        <h1>Admin Profile</h1>
        
        <div class="card" style="max-width: 800px; margin: 0 auto; padding: 1.5rem;">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <!-- Profile Picture Section -->
            <div class="text-center" style="margin-bottom: 2rem;">
                <img src="../uploads/profile_images/<?php echo htmlspecialchars($admin['profile_image']); ?>" 
                     alt="Profile Picture" 
                     class="profile-image"
                     style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto 1.5rem;">
                
                <form method="post" enctype="multipart/form-data" class="upload-form" style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                    <div style="width: 100%; max-width: 300px; text-align: center;">
                        <input type="file" name="profile_image" accept="image/*" class="form-control" style="margin: 0 auto; display: block; width: fit-content;">
                    </div>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <button type="submit" name="upload_image" class="btn">Update Profile Picture</button>
                        <button type="submit" name="reset_image" class="btn" style="background-color: #6c757d;">Reset to Default</button>
                    </div>
                </form>
            </div>
            
            <!-- Admin Information Section -->
            <div class="section-divider"></div>
            <div class="admin-info" style="margin-bottom: 2rem; text-align: center;">
                <h2 style="margin-bottom: 1.5rem;">Admin Information</h2>
                <table class="table" style=" margin: 0 auto;">
                    <tr>
                        <th style="width: 150px;">Username:</th>
                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                    </tr>
                    <tr>
                        <th>Full Name:</th>
                        <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Member Since:</th>
                        <td><?php echo date('F j, Y', strtotime($admin['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Change Password Section -->
            <div class="section-divider"></div>
            <div class="password-section" style="text-align: center;">
                <h2 style="margin-bottom: 1.5rem;">Change Password</h2>
                <form method="post" action="" style="margin: 0 auto; text-align: left;">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="change_password" class="btn">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Handle profile image reset
if (isset($_POST['reset_image'])) {
    // Only delete current image if it's not already default.jpg
    if ($user['profile_image'] !== 'default.jpg') {
        $old_image = '../uploads/profile_images/' . $user['profile_image'];
        if (file_exists($old_image)) {
            unlink($old_image);
        }
        mysqli_query($conn, "UPDATE users SET profile_image = 'default.jpg' WHERE id = $user_id");
        $user['profile_image'] = 'default.jpg';
        $success = "Profile picture reset to default!";
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
            $error = "File size must be less than 2MB.";
        } else if (in_array($filetype, $allowed)) {
            // Create unique filename
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $filetype;
            $upload_path = '../uploads/profile_images/' . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists and is not the default
                if ($user['profile_image'] !== 'default.jpg') {
                    $old_image = '../uploads/profile_images/' . $user['profile_image'];
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
                
                // Update database
                mysqli_query($conn, "UPDATE users SET profile_image = '$new_filename' WHERE id = $user_id");
                $user['profile_image'] = $new_filename; // Update current page display
                $success = "Profile image updated successfully!";
            } else {
                $error = "Error uploading image. Please try again.";
            }
        } else {
            $error = "Invalid file type. Please upload a jpg, jpeg, png, or gif file.";
        }
    } else {
        $error = "Please select an image to upload.";
    }
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    if (md5($current_password) === $user['password']) {
            $sql = "UPDATE users SET full_name = '$full_name', email = '$email'";
            
            if (!empty($new_password)) {
                $md5_password = md5($new_password);
                $sql .= ", password = '$md5_password'";
            }
        
        $sql .= " WHERE id = $user_id";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Profile updated successfully!";
            // Refresh user data
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
    } else {
        $error = "Current password is incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Library Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <?php include '../includes/alert_handler.php'; ?>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="container">
        <h1>My Profile</h1>
        
        <div class="card" style="max-width: 800px; margin: 0 auto; padding: 1.5rem;">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <!-- Profile Picture Section -->
            <div class="text-center" style="margin-bottom: 2rem;">
                <img src="../uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                     alt="Profile Picture" 
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
            
            <!-- Account Information -->
            <div class="section-divider"></div>
            <div class="account-info" style="margin-bottom: 2rem; text-align: center;">
                <h2 style="margin-bottom: 1.5rem;">Account Information</h2>
                <table class="table" style="width: 100%; margin: 0 auto;">
                    <tr>
                        <th style="width: 150px;">Username:</th>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span style="color: <?php echo $user['status'] === 'active' ? 'green' : 'red'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Member Since:</th>
                        <td><?php echo date('F j, Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- User Profile Information -->
            <div class="section-divider"></div>
            <div class="profile-info" style="margin-bottom: 2rem; text-align: center;">
                <h2 style="margin-bottom: 1.5rem;">Profile Information</h2>
                <form method="POST" style="text-align: left;">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" class="form-control" 
                           value="<?php echo $user['username']; ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" name="full_name" id="full_name" class="form-control" 
                           value="<?php echo $user['full_name']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           value="<?php echo $user['email']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" name="current_password" id="current_password" 
                           class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password (leave blank to keep current):</label>
                    <input type="password" name="new_password" id="new_password" 
                           class="form-control">
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_profile" class="btn">Update Profile</button>
                </div>
            </form>
        </div>

        <!-- Recent Activity Section -->
        <div class="section-divider"></div>
        <div class="recent-activity" style="margin-top: 2rem; text-align: center;">
            <h2 style="margin-bottom: 1.5rem;">Recent Activity</h2>
            <?php
            $activity_sql = "SELECT bi.*, b.title, 
                           DATE_FORMAT(bi.created_at, '%M %d, %Y') as formatted_date,
                           COALESCE(bi.fine, 0) as fine_amount
                           FROM book_issues bi 
                           JOIN books b ON bi.book_id = b.id 
                           WHERE bi.user_id = $user_id 
                           ORDER BY bi.created_at DESC 
                           LIMIT 5";
            $activity_result = mysqli_query($conn, $activity_sql);
            
            if (mysqli_num_rows($activity_result) > 0):
            ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Book</th>
                            <th>Status</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($activity = mysqli_fetch_assoc($activity_result)): ?>
                        <tr>
                            <td><?php echo $activity['formatted_date']; ?></td>
                            <td><?php echo htmlspecialchars($activity['title']); ?></td>
                            <td>
                                <span style="color: 
                                    <?php
                                    switch($activity['status']) {
                                        case 'pending': echo '#ffc107'; break;
                                        case 'approved': echo '#28a745'; break;
                                        case 'rejected': echo '#dc3545'; break;
                                        case 'returned': echo '#17a2b8'; break;
                                    }
                                    ?>">
                                    <?php echo ucfirst($activity['status']); ?>
                                </span>
                            </td>
                            <td style="color: <?php echo $activity['fine_amount'] > 0 ? '#dc3545' : '#28a745'; ?>">
                                ₹<?php echo number_format($activity['fine_amount'], 2); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No recent activity found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

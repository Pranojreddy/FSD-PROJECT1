<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user details
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Check if email already exists for another user
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $user_id");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email already in use by another account!";
    } else {
        $query = "UPDATE users SET full_name = '$full_name', email = '$email', phone = '$phone', address = '$address' WHERE id = $user_id";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $success = "Profile updated successfully!";
            // Refresh user data
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
        } else {
            $error = "Update failed: " . mysqli_error($conn);
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if ($user['password'] != $current) {
        $error = "Current password is incorrect!";
    } elseif ($new != $confirm) {
        $error = "New passwords do not match!";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters!";
    } else {
        mysqli_query($conn, "UPDATE users SET password = '$new' WHERE id = $user_id");
        $success = "Password changed successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Inventory System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .profile-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .profile-section h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }
        .info-group {
            margin-bottom: 15px;
        }
        .info-group label {
            font-weight: bold;
            color: #7f8c8d;
            display: block;
            margin-bottom: 5px;
        }
        .info-group p {
            color: #2c3e50;
            font-size: 16px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .stats-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .stats-box h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>My Profile</h1>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="profile-container">
                <!-- Profile Information -->
                <div class="profile-section">
                    <h2>Profile Information</h2>
                    <div class="info-group">
                        <label>Username</label>
                        <p><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Role</label>
                        <p><span class="badge badge-<?php echo $user['role'] == 'admin' ? 'danger' : 'success'; ?>"><?php echo ucfirst($user['role']); ?></span></p>
                    </div>
                    <div class="info-group">
                        <label>Member Since</label>
                        <p><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Status</label>
                        <p><span class="badge badge-success"><?php echo ucfirst($user['status']); ?></span></p>
                    </div>
                    
                    <h2 style="margin-top: 30px;">Edit Profile</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
                
                <!-- Change Password & Stats -->
                <div class="profile-section">
                    <h2>Change Password</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                    </form>
                    
                    <div class="stats-box">
                        <h3>Account Statistics</h3>
                        <?php
                        $stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as sales_count, COALESCE(SUM(total_amount), 0) as sales_total FROM sales WHERE user_id = $user_id"));
                        ?>
                        <p><strong>Total Sales Made:</strong> <?php echo $stats['sales_count']; ?></p>
                        <p><strong>Total Sales Value:</strong> $<?php echo number_format($stats['sales_total'], 2); ?></p>
                        
                        <?php
                        $stock_ops = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as ops FROM stock_transactions WHERE user_id = $user_id"));
                        ?>
                        <p><strong>Stock Operations:</strong> <?php echo $stock_ops['ops']; ?></p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
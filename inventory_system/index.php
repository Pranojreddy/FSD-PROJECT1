<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Check in database (username or email)
    $query = "SELECT * FROM users WHERE (username = '$username' OR email = '$username') AND status = 'active'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        // Simple password check
        if ($password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            
            redirect('dashboard.php');
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Username/Email not found or account inactive!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 1200px;
        }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        .card-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .card h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2em;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 15px;
        }
        .btn:hover {
            background: #5a67d8;
        }
        .error {
            background: #fff5f5;
            color: #c53030;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #feb2b2;
            font-size: 14px;
        }
        .toggle-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .toggle-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
        }
        .toggle-link a:hover {
            text-decoration: underline;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 50px;
            color: white;
        }
        .feature {
            text-align: center;
            padding: 25px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }
        .feature h3 {
            margin-bottom: 12px;
            font-size: 1.4em;
        }
        .feature p {
            opacity: 0.9;
            line-height: 1.6;
        }
        .demo-credentials {
            background: #f7fafc;
            border-radius: 8px;
            padding: 15px;
            margin-top: 25px;
            font-size: 13px;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        .demo-credentials p {
            margin: 5px 0;
        }
        .demo-credentials strong {
            color: #2d3748;
        }
        @media (max-width: 768px) {
            .features {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Inventory Management System</h1>
            <p>Efficiently manage your stock, sales, and suppliers</p>
        </div>
        
        <div class="card-container">
            <!-- Login Card -->
            <div class="card">
                <h2>Welcome Back</h2>
                <?php if ($error): ?>
                    <div class="error">⚠️ <?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Username or Email</label>
                        <input type="text" name="username" placeholder="Enter your username or email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" name="login" class="btn">Login to Dashboard</button>
                </form>
                
                <div class="demo-credentials">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>👤 Admin: username: admin / password: admin123</p>
                    <p>📝 Register new account for staff access</p>
                </div>
                
                <div class="toggle-link">
                    Don't have an account? <a href="register.php">Create Account →</a>
                </div>
            </div>
        </div>
        
        <div class="features">
            <div class="feature">
                <h3>📦 Stock Management</h3>
                <p>Track inventory levels in real-time with automated low stock alerts</p>
            </div>
            <div class="feature">
                <h3>💰 Sales Processing</h3>
                <p>Process sales quickly, generate invoices automatically</p>
            </div>
            <div class="feature">
                <h3>📊 Reports & Analytics</h3>
                <p>Generate detailed reports on stock movement and sales performance</p>
            </div>
        </div>
    </div>
</body>
</html>
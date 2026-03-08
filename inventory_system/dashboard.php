<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Get user-specific statistics
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Total products (visible to all)
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];

// Total suppliers
$total_suppliers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM suppliers"))['count'];

// Sales statistics - user specific for staff, all for admin
if ($user_role == 'admin') {
    $total_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM sales"))['total'] ?? 0;
    $user_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM sales"))['count'] ?? 0;
} else {
    $total_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM sales WHERE user_id = $user_id"))['total'] ?? 0;
    $user_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM sales WHERE user_id = $user_id"))['count'] ?? 0;
}

// Low stock items
$low_stock = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM stock s JOIN products p ON s.product_id = p.id WHERE s.quantity <= p.reorder_level"
))['count'];

// Get recent products
$recent_products = mysqli_query($conn, 
    "SELECT p.*, c.category_name, s.quantity FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     LEFT JOIN stock s ON p.id = s.product_id 
     ORDER BY p.created_at DESC LIMIT 5"
);

// Get recent sales for this user (or all for admin)
if ($user_role == 'admin') {
    $recent_sales = mysqli_query($conn, 
        "SELECT s.*, u.username FROM sales s 
         LEFT JOIN users u ON s.user_id = u.id 
         ORDER BY s.sale_date DESC LIMIT 5"
    );
} else {
    $recent_sales = mysqli_query($conn, 
        "SELECT * FROM sales WHERE user_id = $user_id ORDER BY sale_date DESC LIMIT 5"
    );
}

// Welcome message based on time of day
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 17) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <?php echo $greeting . ', ' . $_SESSION['full_name']; ?>!
                </div>
            </div>
            
            <?php displayMessage(); ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-details">
                        <h3><?php echo $total_products; ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-details">
                        <h3><?php echo $total_suppliers; ?></h3>
                        <p>Suppliers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3>$<?php echo number_format($total_sales, 2); ?></h3>
                        <p><?php echo ($user_role == 'admin') ? 'Total Sales' : 'My Sales'; ?></p>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-details">
                        <h3><?php echo $low_stock; ?></h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="recent-section">
                    <h2>Recent Products</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = mysqli_fetch_assoc($recent_products)): ?>
                            <tr>
                                <td><?php echo $product['product_code']; ?></td>
                                <td><?php echo $product['product_name']; ?></td>
                                <td><?php echo $product['category_name'] ?? 'N/A'; ?></td>
                                <td><?php echo $product['quantity'] ?? 0; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="recent-section">
                    <h2>Recent Sales</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($sale = mysqli_fetch_assoc($recent_sales)): ?>
                            <tr>
                                <td><?php echo $sale['invoice_no']; ?></td>
                                <td><?php echo $sale['customer_name'] ?? 'Walk-in'; ?></td>
                                <td>$<?php echo number_format($sale['total_amount'], 2); ?></td>
                                <td><?php echo date('M d, H:i', strtotime($sale['sale_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($user_role == 'staff'): ?>
            <div style="margin-top: 20px; padding: 15px; background: #ebf8ff; border-radius: 8px; border-left: 4px solid #4299e1;">
                <strong>📌 Your Stats:</strong> You have made <?php echo $user_sales; ?> sales totaling $<?php echo number_format($total_sales, 2); ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
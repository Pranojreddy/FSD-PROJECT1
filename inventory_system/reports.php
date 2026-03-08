<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Get stock report
$stock_report = mysqli_query($conn, "
    SELECT p.product_code, p.product_name, c.category_name, s.quantity, p.reorder_level,
           CASE WHEN s.quantity <= p.reorder_level THEN 'Low Stock' ELSE 'Normal' END as status
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN stock s ON p.id = s.product_id
    ORDER BY status DESC, p.product_name
");

// Get sales summary
$sales_summary = mysqli_query($conn, "
    SELECT DATE(sale_date) as date, COUNT(*) as transactions, SUM(total_amount) as total
    FROM sales
    GROUP BY DATE(sale_date)
    ORDER BY date DESC
    LIMIT 10
");

$total_inventory = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(p.unit_price * s.quantity) as total 
    FROM products p 
    JOIN stock s ON p.id = s.product_id
"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Reports</h1>
            </div>
            
            <div class="summary-box">
                <h3>Total Inventory Value: $<?php echo number_format($total_inventory ?? 0, 2); ?></h3>
            </div>
            
            <div class="report-section">
                <h2>Stock Status Report</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($stock_report)): ?>
                        <tr>
                            <td><?php echo $row['product_code']; ?></td>
                            <td><?php echo $row['product_name']; ?></td>
                            <td><?php echo $row['category_name'] ?? 'N/A'; ?></td>
                            <td><?php echo $row['quantity'] ?? 0; ?></td>
                            <td><?php echo $row['reorder_level']; ?></td>
                            <td>
                                <span class="badge <?php echo $row['status'] == 'Low Stock' ? 'badge-danger' : 'badge-success'; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="report-section">
                <h2>Recent Sales Summary</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transactions</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($sales_summary)): ?>
                        <tr>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['transactions']; ?></td>
                            <td>$<?php echo number_format($row['total'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
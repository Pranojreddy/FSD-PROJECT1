<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>Inventory System</h2>
        <p>Welcome, <?php echo $_SESSION['full_name']; ?></p>
        <p style="font-size: 12px; color: #bdc3c7;">Role: <?php echo ucfirst($_SESSION['role']); ?></p>
    </div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        <li><a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">📦 Products</a></li>
        <li><a href="suppliers.php" class="<?php echo $current_page == 'suppliers.php' ? 'active' : ''; ?>">🏢 Suppliers</a></li>
        <li><a href="add_stock.php" class="<?php echo $current_page == 'add_stock.php' ? 'active' : ''; ?>">➕ Add Stock</a></li>
        <li><a href="sell_product.php" class="<?php echo $current_page == 'sell_product.php' ? 'active' : ''; ?>">💰 Sell Product</a></li>
        <li><a href="reports.php" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">📈 Reports</a></li>
        <li><a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">👤 My Profile</a></li>
        <li class="logout"><a href="logout.php">🚪 Logout</a></li>
    </ul>
</div>
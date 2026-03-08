<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Get all products
$products = mysqli_query($conn, "SELECT p.*, st.quantity FROM products p LEFT JOIN stock st ON p.id = st.product_id");

// Handle stock addition
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $unit_price = (float)$_POST['unit_price'];
    $reference = mysqli_real_escape_string($conn, $_POST['reference']);
    
    $total = $quantity * $unit_price;
    
    mysqli_begin_transaction($conn);
    
    // Update stock
    mysqli_query($conn, "UPDATE stock SET quantity = quantity + $quantity WHERE product_id = $product_id");
    
    // Record transaction
    mysqli_query($conn, "INSERT INTO stock_transactions (product_id, transaction_type, quantity, unit_price, total_amount, reference_no, user_id) 
                         VALUES ($product_id, 'IN', $quantity, $unit_price, $total, '$reference', {$_SESSION['user_id']})");
    
    mysqli_commit($conn);
    
    $_SESSION['success'] = "Stock added successfully!";
    redirect('add_stock.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock - Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Add Stock</h1>
            </div>
            
            <?php displayMessage(); ?>
            
            <div class="form-container">
                <form method="POST" class="stock-form">
                    <div class="form-group">
                        <label>Select Product</label>
                        <select name="product_id" required>
                            <option value="">Choose a product</option>
                            <?php while($p = mysqli_fetch_assoc($products)): ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo $p['product_name']; ?> (Current Stock: <?php echo $p['quantity'] ?? 0; ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Unit Price</label>
                        <input type="number" step="0.01" name="unit_price" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Reference (PO #)</label>
                        <input type="text" name="reference" placeholder="PO-2024-001" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Add Stock</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Get products in stock
$products = mysqli_query($conn, "
    SELECT p.*, st.quantity 
    FROM products p 
    JOIN stock st ON p.id = st.product_id 
    WHERE st.quantity > 0
");

// Process sale
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer = mysqli_real_escape_string($conn, $_POST['customer_name']) ?: 'Walk-in Customer';
    $payment = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    
    $invoice_no = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
    $total_amount = 0;
    
    mysqli_begin_transaction($conn);
    
    try {
        // Calculate total and validate stock
        for ($i = 0; $i < count($product_ids); $i++) {
            if ($quantities[$i] > 0) {
                $pid = $product_ids[$i];
                $qty = $quantities[$i];
                
                // Check stock
                $stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT quantity FROM stock WHERE product_id = $pid"));
                if ($stock['quantity'] < $qty) {
                    throw new Exception("Insufficient stock for product ID: $pid");
                }
                
                // Get price
                $price = mysqli_fetch_assoc(mysqli_query($conn, "SELECT unit_price FROM products WHERE id = $pid"));
                $total_amount += $price['unit_price'] * $qty;
            }
        }
        
        // Create sale
        mysqli_query($conn, "INSERT INTO sales (invoice_no, customer_name, total_amount, payment_method, user_id) 
                            VALUES ('$invoice_no', '$customer', $total_amount, '$payment', {$_SESSION['user_id']})");
        $sale_id = mysqli_insert_id($conn);
        
        // Process each item
        for ($i = 0; $i < count($product_ids); $i++) {
            if ($quantities[$i] > 0) {
                $pid = $product_ids[$i];
                $qty = $quantities[$i];
                
                $price = mysqli_fetch_assoc(mysqli_query($conn, "SELECT unit_price FROM products WHERE id = $pid"));
                $subtotal = $price['unit_price'] * $qty;
                
                // Add sale item
                mysqli_query($conn, "INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) 
                                    VALUES ($sale_id, $pid, $qty, {$price['unit_price']}, $subtotal)");
                
                // Update stock
                mysqli_query($conn, "UPDATE stock SET quantity = quantity - $qty WHERE product_id = $pid");
                
                // Record transaction
                mysqli_query($conn, "INSERT INTO stock_transactions (product_id, transaction_type, quantity, unit_price, total_amount, reference_no, user_id) 
                                    VALUES ($pid, 'OUT', $qty, {$price['unit_price']}, $subtotal, '$invoice_no', {$_SESSION['user_id']})");
            }
        }
        
        mysqli_commit($conn);
        $_SESSION['success'] = "Sale completed! Invoice: $invoice_no";
        redirect('sell_product.php');
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Product - Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Sell Products</h1>
            </div>
            
            <?php displayMessage(); ?>
            
            <form method="POST" id="saleForm">
                <div class="sale-info">
                    <div class="form-group">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" placeholder="Walk-in Customer">
                    </div>
                    
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method">
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>
                
                <div class="products-section">
                    <h3>Products</h3>
                    <table class="table" id="productTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Available</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="productRows">
                            <!-- Rows will be added here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                                <td><strong id="grandTotal">$0.00</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <button type="button" onclick="addRow()" class="btn btn-primary">Add Product</button>
                </div>
                
                <button type="submit" class="btn btn-success" style="margin-top: 20px;">Complete Sale</button>
            </form>
        </main>
    </div>
    
    <script>
        let products = <?php 
            $products_array = [];
            mysqli_data_seek($products, 0);
            while($p = mysqli_fetch_assoc($products)) {
                $products_array[] = $p;
            }
            echo json_encode($products_array);
        ?>;
        
        function addRow() {
            let tbody = document.getElementById('productRows');
            let row = document.createElement('tr');
            
            let select = '<select name="product_id[]" class="product-select" onchange="updateRow(this)">';
            select += '<option value="">Select Product</option>';
            products.forEach(p => {
                select += `<option value="${p.id}" data-price="${p.unit_price}" data-stock="${p.quantity}">${p.product_name}</option>`;
            });
            select += '</select>';
            
            row.innerHTML = `
                <td>${select}</td>
                <td class="price">$0.00</td>
                <td class="stock">0</td>
                <td><input type="number" name="quantity[]" min="0" value="0" onchange="calculateSubtotal(this)" class="qty-input"></td>
                <td class="subtotal">$0.00</td>
                <td><button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm">Remove</button></td>
            `;
            
            tbody.appendChild(row);
        }
        
        function updateRow(select) {
            let row = select.closest('tr');
            let option = select.options[select.selectedIndex];
            let price = option.dataset.price || 0;
            let stock = option.dataset.stock || 0;
            
            row.querySelector('.price').textContent = '$' + parseFloat(price).toFixed(2);
            row.querySelector('.stock').textContent = stock;
            calculateSubtotal(row.querySelector('.qty-input'));
        }
        
        function calculateSubtotal(input) {
            let row = input.closest('tr');
            let price = parseFloat(row.querySelector('.price').textContent.replace('$', '')) || 0;
            let qty = parseInt(input.value) || 0;
            let subtotal = price * qty;
            
            row.querySelector('.subtotal').textContent = '$' + subtotal.toFixed(2);
            calculateTotal();
        }
        
        function calculateTotal() {
            let subtotals = document.querySelectorAll('.subtotal');
            let total = 0;
            subtotals.forEach(s => {
                total += parseFloat(s.textContent.replace('$', '')) || 0;
            });
            document.getElementById('grandTotal').textContent = '$' + total.toFixed(2);
        }
        
        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateTotal();
        }
        
        // Add first row on page load
        window.onload = function() {
            addRow();
        };
    </script>
</body>
</html>
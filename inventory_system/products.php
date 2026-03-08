<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Add product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_code = mysqli_real_escape_string($conn, $_POST['product_code']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = (int)$_POST['category_id'] ?: 'NULL';
    $supplier_id = (int)$_POST['supplier_id'] ?: 'NULL';
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $unit_price = (float)$_POST['unit_price'];
    $reorder_level = (int)$_POST['reorder_level'];
    
    $query = "INSERT INTO products (product_code, product_name, category_id, supplier_id, description, unit_price, reorder_level) 
              VALUES ('$product_code', '$product_name', $category_id, $supplier_id, '$description', $unit_price, $reorder_level)";
    
    if (mysqli_query($conn, $query)) {
        $product_id = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO stock (product_id, quantity) VALUES ($product_id, 0)");
        $_SESSION['success'] = "Product added successfully!";
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
    }
    redirect('products.php');
}

// Get all products
$products = mysqli_query($conn, "
    SELECT p.*, c.category_name, s.supplier_name, st.quantity 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    LEFT JOIN stock st ON p.id = st.product_id
    ORDER BY p.id DESC
");

// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
$suppliers = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY supplier_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Products</h1>
                <button onclick="showModal()" class="btn btn-primary">Add Product</button>
            </div>
            
            <?php displayMessage(); ?>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td><?php echo $p['product_code']; ?></td>
                        <td><?php echo $p['product_name']; ?></td>
                        <td><?php echo $p['category_name'] ?? '-'; ?></td>
                        <td><?php echo $p['supplier_name'] ?? '-'; ?></td>
                        <td>$<?php echo number_format($p['unit_price'], 2); ?></td>
                        <td><?php echo $p['quantity'] ?? 0; ?></td>
                        <td>
                            <?php if (($p['quantity'] ?? 0) <= $p['reorder_level']): ?>
                                <span class="badge badge-danger">Low Stock</span>
                            <?php else: ?>
                                <span class="badge badge-success">In Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
    
    <!-- Add Product Modal -->
    <div id="productModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span onclick="hideModal()" class="close">&times;</span>
            <h2>Add New Product</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Product Code</label>
                    <input type="text" name="product_code" required>
                </div>
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="product_name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">Select Category</option>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['category_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Supplier</label>
                    <select name="supplier_id">
                        <option value="">Select Supplier</option>
                        <?php mysqli_data_seek($suppliers, 0); ?>
                        <?php while($sup = mysqli_fetch_assoc($suppliers)): ?>
                        <option value="<?php echo $sup['id']; ?>"><?php echo $sup['supplier_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Unit Price</label>
                    <input type="number" step="0.01" name="unit_price" required>
                </div>
                <div class="form-group">
                    <label>Reorder Level</label>
                    <input type="number" name="reorder_level" value="10">
                </div>
                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            </form>
        </div>
    </div>
    
    <script>
        function showModal() {
            document.getElementById('productModal').style.display = 'block';
        }
        function hideModal() {
            document.getElementById('productModal').style.display = 'none';
        }
    </script>
</body>
</html>
<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Add supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_supplier'])) {
    $name = mysqli_real_escape_string($conn, $_POST['supplier_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_person']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    $query = "INSERT INTO suppliers (supplier_name, contact_person, email, phone, address) 
              VALUES ('$name', '$contact', '$email', '$phone', '$address')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Supplier added successfully!";
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
    }
    redirect('suppliers.php');
}

// Delete supplier
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM suppliers WHERE id = $id");
    $_SESSION['success'] = "Supplier deleted successfully!";
    redirect('suppliers.php');
}

$suppliers = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY supplier_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - Inventory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Suppliers</h1>
                <button onclick="showModal()" class="btn btn-primary">Add Supplier</button>
            </div>
            
            <?php displayMessage(); ?>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($s = mysqli_fetch_assoc($suppliers)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['supplier_name']); ?></td>
                        <td><?php echo htmlspecialchars($s['contact_person'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($s['email'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($s['phone'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($s['address'] ?? '-'); ?></td>
                        <td>
                            <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this supplier?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
    
    <!-- Add Supplier Modal -->
    <div id="supplierModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span onclick="hideModal()" class="close">&times;</span>
            <h2>Add New Supplier</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Supplier Name *</label>
                    <input type="text" name="supplier_name" required>
                </div>
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="3"></textarea>
                </div>
                <button type="submit" name="add_supplier" class="btn btn-primary">Add Supplier</button>
            </form>
        </div>
    </div>
    
    <script>
        function showModal() {
            document.getElementById('supplierModal').style.display = 'block';
        }
        function hideModal() {
            document.getElementById('supplierModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('supplierModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
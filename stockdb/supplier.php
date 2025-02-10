<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "stockdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] == 'add') {
        $supplierName = $_POST['supplierName'];
        $phoneNumber = $_POST['phoneNumber'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("INSERT INTO suppliers (SupplierName, PhoneNumber, Address, LastUpdateTime) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $supplierName, $phoneNumber, $address);

        if ($stmt->execute()) {
            $_SESSION['message'] = "New supplier added successfully";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif ($_POST['action'] == 'update') {
        $supplierId = $_POST['supplier_id'];
        $supplierName = $_POST['supplierName'];
        $phoneNumber = $_POST['phoneNumber'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("UPDATE suppliers SET SupplierName=?, PhoneNumber=?, Address=?, LastUpdateTime=NOW() WHERE SupplierID=?");
        $stmt->bind_param("sssi", $supplierName, $phoneNumber, $address, $supplierId);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Supplier updated successfully";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

if (isset($_GET['delete'])) {
    $supplierId = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM suppliers WHERE SupplierID=?");
    $stmt->bind_param("i", $supplierId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Supplier deleted successfully";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all suppliers
$sql = "SELECT * FROM suppliers";
$result = $conn->query($sql);
$suppliers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

// Retrieve the message from the session
$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Suppliers Details</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info" id="message"><?= $message; ?></div>
        <?php endif; ?>
        <a href="home.php" class="btn btn-success">Home</a>
        <button class="btn btn-secondary" data-toggle="modal" data-target="#addSupplierModal">Add Supplier</button>

        <a href="inventory.php" class="btn btn-primary">Inventory Management</a>
            <a href="issued.php" class="btn btn-primary">Issue Item</a>
            <a href="low_quantity.php" class="btn btn-primary">Low Quantity Items</a>

        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>Supplier ID</th>
                    <th>Supplier Name</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><?= $supplier['SupplierID'] ?></td>
                        <td><?= $supplier['SupplierName'] ?></td>
                        <td><?= $supplier['PhoneNumber'] ?></td>
                        <td><?= $supplier['Address'] ?></td>
                        <td>
                            <button class="btn btn-warning" data-toggle="modal" data-target="#updateSupplierModal" 
                                    data-id="<?= $supplier['SupplierID'] ?>" 
                                    data-name="<?= $supplier['SupplierName'] ?>" 
                                    data-phone="<?= $supplier['PhoneNumber'] ?>" 
                                    data-address="<?= $supplier['Address'] ?>">Edit</button>
                            <a href="?delete=<?= $supplier['SupplierID'] ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Supplier</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label>Supplier Name</label>
                            <input type="text" name="supplierName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phoneNumber" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Supplier</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Supplier Modal -->
    <div class="modal fade" id="updateSupplierModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Supplier</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="supplier_id" id="updateSupplierId">
                        <div class="form-group">
                            <label>Supplier Name</label>
                            <input type="text" name="supplierName" id="updateSupplierName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phoneNumber" id="updatePhoneNumber" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" id="updateAddress" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Supplier</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).on('click', '[data-target="#updateSupplierModal"]', function () {
            const modal = $('#updateSupplierModal');
            modal.find('#updateSupplierId').val($(this).data('id'));
            modal.find('#updateSupplierName').val($(this).data('name'));
            modal.find('#updatePhoneNumber').val($(this).data('phone'));
            modal.find('#updateAddress').val($(this).data('address'));
        });

        setTimeout(() => {
            $('#message').fadeOut();
        }, 3000);
    </script>
</body>
</html>

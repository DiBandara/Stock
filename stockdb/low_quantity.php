<?php
// Database connection
$servername = "localhost"; // or your server address
$username = "root"; // your database username
$password = "root"; // your database password
$dbname = "stockdb"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}// Ensure you have your database connection setup here

// Query to fetch items with low stock compared to their warning level
$sql = "SELECT i.Name, i.Description, i.Category, i.SubCategory, s.SupplierName AS Supplier, i.Cost, i.Quantity, w.warning_quantity
        FROM inventory i
        JOIN warning_level w ON i.ID = w.Item_id
        JOIN suppliers s ON i.SupplierID = s.SupplierID
        WHERE i.Quantity < w.warning_quantity";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Quantity Items</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            margin-top: 50px;
        }
        table {
            background-color: white;
            border: 1px solid #ddd;
        }
        th, td {
            padding: 15px;
            text-align: center;
        }
        th {
            background-color: #f5c6cb;
            color: #721c24;
        }
        td {
            background-color: #ffffff;
        }
        .alert {
            color: #721c24;
            background-color: #f5c6cb;
            border-color: #f5c6cb;
        }
        .col-md-8 {
            

            
            margin-bottom: 20px;


        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="alert alert-danger text-center">Low Quantity Items (Warning)</h1>
    <div class="col-md-8">
            <!-- Home Button -->

   
        <a href="home.php" class="btn btn-success">Home</a>

        <a href="inventory.php" class="btn btn-primary">Inventory Management</a>
            <a href="supplier.php" class="btn btn-primary">Suppliers Details</a>
            <a href="issued.php" class="btn btn-primary">Issue Item</a>
        </div>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>SubCategory</th>
                    <th>Supplier</th>
                    <th>Cost</th>
                    <th>Remaining Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Description']); ?></td>
                        <td><?php echo htmlspecialchars($row['Category']); ?></td>
                        <td><?php echo htmlspecialchars($row['SubCategory']); ?></td>
                        <td><?php echo htmlspecialchars($row['Supplier']); ?></td>
                        <td><?php echo htmlspecialchars($row['Cost']); ?></td>
                        <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            No items with low stock.
        </div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

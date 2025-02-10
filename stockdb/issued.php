<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "stockdb"; // Ensure the correct database name is used

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle item issuance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_issue_form'])) {
    $item_id = $_POST['item_id'];
    $receiver_name = $_POST['receiver_name'];
    $quantity = $_POST['quantity'];
    $issued_date = $_POST['issued_date'];
    $return_date = $_POST['return_date'];
    $return_status = $_POST['return_status']; // "returning" or "not_returning"

    // If the item is not returning, set return_date to NULL
    if ($return_status === 'not_returning') {
        $return_date = NULL;
    }

    // Fetch current quantity from inventory
    $sql = "SELECT quantity FROM inventory WHERE ID = $item_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_quantity = $row['quantity'];

        // Check if enough stock is available
        if ($current_quantity >= $quantity) {
            // Update inventory
            $new_quantity = $current_quantity - $quantity;
            $update_sql = "UPDATE inventory SET quantity = $new_quantity WHERE ID = $item_id";
            if ($conn->query($update_sql) === TRUE) {
                // Insert into issue table
                $last_update_time = date('Y-m-d H:i:s'); // Get current timestamp
                $issue_sql = "INSERT INTO issue (ItemID, ReceiverName, Quantity, IssuedDate, ReturnDate, LastUpdateTime) 
                              VALUES ($item_id, '$receiver_name', $quantity, '$issued_date', " . ($return_date ? "'$return_date'" : "NULL") . ", '$last_update_time')";
                if ($conn->query($issue_sql) === TRUE) {
                    // Redirect after successful submission
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $error_message = "Error: " . $issue_sql . "<br>" . $conn->error;
                }
            } else {
                $error_message = "Error updating inventory: " . $conn->error;
            }
        } else {
            $error_message = "Not enough stock available!";
        }
    } else {
        $error_message = "Item not found!";
    }
}


// Fetch inventory data
$inventory_sql = "SELECT ID, Name, Description, Category, SubCategory, Quantity FROM inventory";
$inventory_result = $conn->query($inventory_sql);
if ($inventory_result === FALSE) {
    $error_message = "Error fetching inventory: " . $conn->error;
}

// Fetch issued items data
$issued_items_sql = "SELECT ii.*, i.Name AS item_name, i.Description AS item_description 
                     FROM issue ii 
                     JOIN inventory i ON ii.ItemID = i.ID";
$issued_items_result = $conn->query($issued_items_sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <a href="issuedprint.php" class="btn btn-primary">print</a>
    <title>Issue Item</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>document.querySelectorAll('input[name="return_status"]').forEach((elem) => {
    elem.addEventListener('change', function() {
        if (this.value === 'not_returning') {
            document.getElementById('return_date_group').style.display = 'none';
        } else {
            document.getElementById('return_date_group').style.display = 'block';
        }
    });
});
</script>
</head>
<body>
    <div class="container mt-5">
        <h2>Issue Item</h2>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <!-- Button to Open the Modal -->
        <div class="col-md-8">
            <!-- Home Button -->

   
        <a href="home.php" class="btn btn-success">Home</a>

        <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#issueItemModal">
    Issue Item
</button>

            <a href="inventory.php" class="btn btn-primary">Inventory Management</a>
            <a href="supplier.php" class="btn btn-primary">Suppliers Details</a>
            
            <a href="low_quantity.php" class="btn btn-primary">Low Quantity Items</a>
        
        
        </div>
        <!-- Modal -->
        <div class="modal fade" id="issueItemModal" tabindex="-1" role="dialog" aria-labelledby="issueItemModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        
                        <h5 class="modal-title" id="issueItemModalLabel">Issue Item Form</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="" method="POST">
    <input type="hidden" name="submit_issue_form" value="1">
    <div class="modal-body">
        <div class="form-group">
            <label for="item_id">Select Item</label>
            <select id="item_id" name="item_id" class="form-control" required>
                <option value="">Select an item</option>
                <?php if ($inventory_result->num_rows > 0): ?>
                    <?php while ($row = $inventory_result->fetch_assoc()): ?>
                        <option value="<?= $row['ID'] ?>">
                            <?= $row['Name'] ?> - <?= $row['Description'] ?> 
                            (Category: <?= $row['Category'] ?>, Sub-Category: <?= $row['SubCategory'] ?>, Available: <?= $row['Quantity'] ?>)
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">No items available</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="receiver_name">Receiver Name</label>
            <input type="text" id="receiver_name" name="receiver_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="issued_date">Issued Date</label>
            <input type="date" id="issued_date" name="issued_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="return_status">Return Status</label>
            <div>
                <label class="radio-inline">
                    <input type="radio" name="return_status" value="returning" checked> Returning
                </label>
                <label class="radio-inline">
                    <input type="radio" name="return_status" value="not_returning"> Not Returning
                </label>
            </div>
        </div>
        <div class="form-group" id="return_date_group">
            <label for="return_date">Return Date</label>
            <input type="date" id="return_date" name="return_date" class="form-control">
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Issue Item</button>
    </div>
</form>

                </div>
            </div>
        </div>

        <!-- Table of Issued Items -->
        <h3 class="mt-5">Issued Items</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Receiver Name</th>
                    <th>Quantity</th>
                    <th>Issued Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $issued_items_sql = "SELECT ii.*, i.Name AS item_name, i.Description AS item_description 
                                     FROM issue ii 
                                     JOIN inventory i ON ii.ItemID = i.ID";
                $issued_items_result = $conn->query($issued_items_sql);

                if ($issued_items_result->num_rows > 0) {
                    while ($row = $issued_items_result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['item_name']} - {$row['item_description']}</td>
                                <td>{$row['ReceiverName']}</td>
                                <td>{$row['Quantity']}</td>
                                <td>{$row['IssuedDate']}</td>
                                <td>{$row['ReturnDate']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No items issued yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Include JS Files -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "stockdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize a success message variable
$successMessage = "";

// Add Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'addItem') {
    $itemName = $_POST['itemCategoryID'];
    $itemDescription = $_POST['itemDescription'];
    $itemCategoryName = $_POST['itemCategoryName'];
    $itemSubCategory = $_POST['itemSubCategory'];
    $itemSupplier = $_POST['itemSupplier'];
    $itemCost = $_POST['itemCost'];
    $itemQuantity = $_POST['itemQuantity'];
    $warningQuantityLevel = $_POST['warningQuantityLevel'];
    $itemAddDate = $_POST['itemAddDate']; // Capture the Item Add Date

    // Insert item data into the inventory table, including ItemAddDate
    $sql = "INSERT INTO inventory (Name, Description, Category, SubCategory, SupplierID, Cost, Quantity, LastUpdateTime, ItemAddDate) 
            VALUES ('$itemName', '$itemDescription', '$itemCategoryName', '$itemSubCategory', '$itemSupplier', '$itemCost', '$itemQuantity', NOW(), '$itemAddDate')";

    if ($conn->query($sql) === TRUE) {
        // Get the last inserted ID from inventory
        $lastItemId = $conn->insert_id;

        // Now insert warning quantity into the warning_level table
        $sql_warning = "INSERT INTO warning_level (Item_id, item_name, warning_quantity) 
                        VALUES ('$lastItemId', '$itemName', '$warningQuantityLevel')";

        if ($conn->query($sql_warning) === TRUE) {
            // Redirect to the same page to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=true");
            exit();
        } else {
            echo "Error inserting warning level: " . $conn->error;
        }
    } else {
        echo "Error inserting item: " . $conn->error;
    }
}



// Display success message if redirected
if (isset($_GET['success']) && $_GET['success'] == 'true') {
    $successMessage = "New item added successfully.";
}

// Edit Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'editItem') {
    $editId = $_POST['edit_id'];
    $editName = $_POST['editItemName'];
    $editDescription = $_POST['editItemDescription'];
    $editCategoryName = $_POST['editItemCategoryName'];
    $editSubCategory = $_POST['editItemSubCategory'];
    $editSupplier = $_POST['editItemSupplier'];
    $editCost = $_POST['editItemCost'];
    $editQuantity = $_POST['editItemQuantity'];
    $editAddDate = $_POST['editItemAddDate']; // Get the new "Item Add Date" from the form

    // Update the item in the inventory table, including the new "Item Add Date"
    $sql = "UPDATE inventory 
            SET Name = '$editName', Description = '$editDescription', Category = '$editCategoryName', SubCategory = '$editSubCategory', SupplierID = '$editSupplier', Cost = '$editCost', Quantity = '$editQuantity', ItemAddDate = '$editAddDate', LastUpdateTime = NOW() 
            WHERE ID = $editId";
    
    if ($conn->query($sql) === TRUE) {
        echo "Item updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}


// Delete Item

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // First, delete the related warning level entry
    $sql_warning = "DELETE FROM warning_level WHERE Item_id = $delete_id";
    if ($conn->query($sql_warning) === TRUE) {
        // Now, delete the item from the inventory
        $sql_inventory = "DELETE FROM inventory WHERE ID = $delete_id";
        if ($conn->query($sql_inventory) === TRUE) {
            echo "Item and its warning level deleted successfully!";
        } else {
            echo "Error deleting from inventory: " . $conn->error;
        }
    } else {
        echo "Error deleting from warning_level: " . $conn->error;
    }
}
// Handle category filter
$searchCategory = "";
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $searchCategory = $_GET['category'];
    // Query to filter inventory by category
    $sql = "SELECT * FROM inventory WHERE Category LIKE '%$searchCategory%'";
} else {
    // Default query to fetch all inventory items
    $sql = "SELECT * FROM inventory";
}


// Query to get total value
$sql = "SELECT SUM(Cost * Quantity) AS TotalValue FROM inventory";
$result = $conn->query($sql);

$totalValue = 0;

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalValue = $row['TotalValue'];
}


$sql_suppliers = "SELECT SupplierID, SupplierName FROM suppliers";
$result_suppliers = $conn->query($sql_suppliers);

$supplierSql = "SELECT SupplierID, SupplierName FROM suppliers";
$supplierResult = $conn->query($supplierSql);

// Fetch inventory items
$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>


function openEditModal(id, name, description, category_name, sub_category_name, supplier_id, cost, quantity, item_add_date) {
    $('#editItemModal').modal('show');
    $('#edit_id').val(id);
    $('#editItemName').val(name);
    $('#editItemDescription').val(description);
    $('#editItemCategoryName').val(category_name);
    $('#editItemCost').val(cost);
    $('#editItemQuantity').val(quantity);
    $('#editItemAddDate').val('' + item_add_date);

    // Populate the supplier dropdown
    $('#editItemSupplier').val(supplier_id); // Ensure this matches the passed supplier_id

    // Load subcategories dynamically
    loadSubCategories(category_name, sub_category_name);
}





        // Ensure subcategories load correctly
        function loadSubCategories(categoryName, selectedSubCategory = '') {
            // Define categories and their sub-categories
            var sub_category_name = {
                "Stationary": ["Paper", "Book", "Envelope", "Writing Item", "Other"],
                "Now Stationary": ["Other"],
                "Machine Items": ["Ribbon", "Ink Cartridge", "Toner"],
                "T Shirt": ["Small", "Medium", "Large", "XL", "XXL", "XXXL"]
            };

            // Clear the previous sub-category options
            var subCategoryDropdown = $('#itemSubCategory');
            var editSubCategoryDropdown = $('#edititemSubCategory');
            subCategoryDropdown.empty();
            editSubCategoryDropdown.empty();
            
            // Add a default option
            subCategoryDropdown.append('<option value="">Select Sub-Category</option>');
            editSubCategoryDropdown.append('<option value="">Select Sub-Category</option>');
            
            // Populate the sub-category dropdown based on the selected category
            if (categoryName && sub_category_name[categoryName]) {
                sub_category_name[categoryName].forEach(function(sub_category) {
                    var selected = sub_category === selectedSubCategory ? 'selected' : '';
                    subCategoryDropdown.append('<option value="' + sub_category + '" ' + selected + '>' + sub_category + '</option>');
                    editSubCategoryDropdown.append('<option value="' + sub_category + '" ' + selected + '>' + sub_category + '</option>');
                });
            }
        }
    </script>
    
    <script>
        // Hide success message after 3 seconds
        $(document).ready(function () {
            setTimeout(function () {
                $('.alert-success').fadeOut('slow');
            }, 3000);
        });
    </script>
</head>
<body>


    <script src="script.js"></script>
    
<div class="container mt-5">
<a href="inventoryprint.php" class="btn btn-primary">print</a>
    <h2>Inventory Management</h2>

    <div class="row mb-3">
        
        <div class="col-md-8">
            <!-- Home Button -->

   
        <a href="home.php" class="btn btn-success">Home</a>

            <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#addItemModal">
                Add New Item
            </button>
            <a href="supplier.php" class="btn btn-primary">Suppliers Details</a>
            <a href="issued.php" class="btn btn-primary">Issue Item</a>
            <a href="low_quantity.php" class="btn btn-primary">Low Quantity Items</a>
            
        </div>
        <div class="col-md-4 text-right">
        <form class="form-inline" method="GET" action="inventory.php">
                <input class="form-control" type="text" name="search" placeholder="Search by Category" value="<?php echo htmlspecialchars($searchCategory); ?>" />
                <button class="btn btn-outline-success ml-2" type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="container mt-5">
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <!-- Add Item Modal -->
        

        <div class="modal fade" id="addItemModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add New Item</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="action" value="addItem">
                    <div class="form-group">
                        <label for="itemCategoryID">Name:</label>
                        <input type="text" class="form-control" name="itemCategoryID" required>
                    </div>
                    <div class="form-group">
                        <label for="itemDescription">Description:</label>
                        <input type="text" class="form-control" name="itemDescription" required>
                    </div>
                    <div class="form-group">
                        <label for="itemCategoryName">Category:</label>
                        <select id="itemCategoryName" class="form-control" name="itemCategoryName" onchange="loadSubCategories(this.value)" required>
                            <option value="">Select Category</option>
                            <option value="Stationary">Stationary</option>
                            <option value="Now Stationary">Non Stationary</option>
                            <option value="Machine Items">Machine Items</option>
                            <option value="T Shirt">T Shirt</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="itemSubCategory">Sub-Category:</label>
                        <select id="itemSubCategory" class="form-control" name="itemSubCategory" required>
                            <option value="">Select Sub-Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="itemSupplier">Supplier:</label>
                        <select id="itemSupplier" class="form-control" name="itemSupplier" required>
                            <option value="">Select Supplier</option>
                            <?php
                            // Fetch suppliers from the database
                            $sql_suppliers = "SELECT SupplierID, SupplierName FROM suppliers";
                            $result_suppliers = $conn->query($sql_suppliers);

                            if ($result_suppliers->num_rows > 0) {
                                while ($row = $result_suppliers->fetch_assoc()) {
                                    // Compare with the supplier_id passed to the modal
                                    $selected = ($row['SupplierID'] == $_POST['supplier_id']) ? 'selected' : '';
                                    echo "<option value='{$row['SupplierID']}' $selected>{$row['SupplierID']} - {$row['SupplierName']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="itemCost">Cost:</label>
                        <input type="text" class="form-control" name="itemCost" required>
                    </div>
                    <div class="form-group">
                        <label for="itemQuantity">Quantity:</label>
                        <input type="text" class="form-control" name="itemQuantity" required>
                    </div>
                    <div class="form-group">
                        <label for="warningQuantityLevel">Warning Quantity Level:</label>
                        <input type="number" class="form-control" name="warningQuantityLevel" required>
                    </div>
                    <div class="form-group">
                        <label for="itemAddDate">Item Add Date:</label>
                        <input type="date" class="form-control" name="itemAddDate" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Item</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="inventory.php">
                        <input type="hidden" name="action" value="editItem">
                        <input type="hidden" id="edit_id" name="edit_id">   
                        <div class="form-group">
                            <label for="editItemName">Name:</label>
                            <input type="text" class="form-control" id="editItemName" name="editItemName" required>
                        </div>
                        <div class="form-group">
                            <label for="editItemDescription">Description:</label>
                            <input type="text" class="form-control" id="editItemDescription" name="editItemDescription" required>
                        </div>
                        <div class="form-group">
                            <label for="editItemCategoryName">Category:</label>
                            <select id="editItemCategoryName" class="form-control" name="editItemCategoryName" onchange="loadSubCategories(this.value, $('#editItemSubCategory').val())" required>
                                <option value="">Select Category</option>
                                <option value="Stationary">Stationary</option>
                                <option value="Now Stationary">Non Stationary</option>
                                <option value="Machine Items">Machine Items</option>
                                <option value="T Shirt">T Shirt</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edititemSubCategory">Sub-Category:</label>
                            <select id="edititemSubCategory" class="form-control" name="editItemSubCategory" required>
                                <option value="">Select Sub-Category</option>
                            </select>
                        </div>
                        <div class="form-group">
    <label for="editItemSupplier">Supplier:</label>
    <select id="editItemSupplier" class="form-control" name="editItemSupplier" required>
        <option value="">Select Supplier</option>
        <?php
        if ($supplierResult->num_rows > 0) {
            while ($row = $supplierResult->fetch_assoc()) {
                echo "<option value='" . $row['SupplierID'] . "'>" . $row['SupplierID'] . " - " . $row['SupplierName'] . "</option>";
            }
        }
        ?>
    </select>
</div>

                        <div class="form-group">
                            <label for="editItemCost">Cost:</label>
                            <input type="text" class="form-control" id="editItemCost" name="editItemCost" required>
                        </div>
                        <div class="form-group">
                            <label for="editItemQuantity">Quantity:</label>
                            <input type="text" class="form-control" id="editItemQuantity" name="editItemQuantity" required>
                        </div>
                        <div class="form-group">
                        <label for="editItemAddDate">Item Add Date:</label>
                        <input type="date" class="form-control" id="editItemAddDate" name="editItemAddDate" required>
                    </div>
                        <button type="submit" class="btn btn-primary">Update Item</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Category</th>
                <th>SubCategory</th>
                <th>Supplier</th>
                <th>Cost</th>
                <th>Quantity</th>
                <th>Item Add Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['Name']}</td>
                            <td>{$row['Description']}</td>
                            <td>{$row['Category']}</td>
                            <td>{$row['SubCategory']}</td>
                            <td>{$row['SupplierID']}</td>
                            <td>{$row['Cost']}</td>
                            <td>{$row['Quantity']}</td>
                            <td>{$row['ItemAddDate']}</td>
                            <td>
                             <button class='btn btn-warning' onclick=\"openEditModal('{$row['ID']}', '{$row['Name']}', '{$row['Description']}', '{$row['Category']}', '{$row['SubCategory']}', '{$row['SupplierID']}', '{$row['Cost']}', '{$row['Quantity']}', '{$row['ItemAddDate']}')\">Edit</button>
   
                            <a href='inventory.php?delete_id={$row['ID']}' class='btn btn-danger'>Delete</a>
                            </td>
                        </tr>";
                }
            }
            ?>
        </tbody>
    </table>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>Inventory Total Value</h4>
            </div>
            <div class="card-body">
                <p class="fs-5">The total value of the inventory is: <strong>Rs.<?= number_format($totalValue, 2); ?></strong></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>

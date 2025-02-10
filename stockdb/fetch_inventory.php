<?php
header('Content-Type: application/json');
include 'db.php';

// SQL query to get data from the 'inventory' table
$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);

$inventory = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $inventory[] = $row; // Add each row to the array
    }
} else {
    echo json_encode(["message" => "No records found"]);
    exit;
}

echo json_encode($inventory); // Return the data as JSON
$conn->close();
?>

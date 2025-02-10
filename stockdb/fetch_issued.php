<?php
header('Content-Type: application/json');

$servername = "localhost"; // Update with your database server
$username = "root"; // Update with your database username
$password = "root"; // Update with your database password
$dbname = "stockdb"; // Update with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["message" => "Connection failed: " . $conn->connect_error]));
}

$sql = "SELECT 
            issue.ID,
            inventory.name AS ItemName,
            issue.ReceiverName,
            issue.Quantity,
            issue.IssuedDate,
            issue.ReturnDate,
            issue.LastUpdateTime
        FROM issue
        INNER JOIN inventory ON issue.ItemID = inventory.ID";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode(["message" => "No issued items found."]);
}

$conn->close();
?>

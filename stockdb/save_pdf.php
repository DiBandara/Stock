<?php
// Set the time zone to India Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Database connection (Make sure your database details are correct)
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "stockdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch inventory data from the database
function fetchInventoryData($conn) {
    $sql = "SELECT * FROM inventory"; // Replace with your actual table name and query
    $result = $conn->query($sql);
    $inventoryData = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $inventoryData[] = $row;
        }
    }

    return $inventoryData;
}

// Function to generate and save PDF using Google Chrome Headless
function generate_and_save_pdf($conn) {
    // Fetch inventory data
    $inventoryData = fetchInventoryData($conn);

    // Generate HTML content for the PDF
    $htmlContent = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h1>Inventory Report</h1>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>SubCategory</th>
                        <th>Cost</th>
                        <th>SupplierID</th>
                        <th>Quantity</th>
                        <th>ItemAddDate</th>
                    </tr>
                </thead>
                <tbody>";

    foreach ($inventoryData as $item) {
        $htmlContent .= "
        <tr>
            <td>{$item['ID']}</td>
            <td>{$item['Name']}</td>
            <td>{$item['Description']}</td>
            <td>{$item['Category']}</td>
            <td>{$item['SubCategory']}</td>
            <td>{$item['Cost']}</td>
            <td>{$item['SupplierID']}</td>
            <td>{$item['Quantity']}</td>
            <td>{$item['ItemAddDate']}</td>
        </tr>";
    }

    $htmlContent .= "
                </tbody>
            </table>
        </body>
    </html>";

    // Use current date and time for the file name
    $file_name = 'inventory_' . date('Y-m-d_H-i-s') . '.pdf';
    $file_path = 'pdfs/' . $file_name;

    // Create the file path for Chrome to print the PDF
    $cmd = "echo '{$htmlContent}' | google-chrome --headless --disable-gpu --print-to-pdf={$file_path} --no-sandbox";

    // Execute the command to generate the PDF
    exec($cmd);

    // Insert the file details into the database
    $stmt = $conn->prepare("INSERT INTO saved_pdfs (file_name, file_path, generated_at) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $file_name, $file_path, date('Y-m-d H:i:s'));
    $stmt->execute();
    $stmt->close();
}

// Automatically generate and save PDF every 1 minute
while (true) {
    generate_and_save_pdf($conn);
    sleep(60); // Wait for 60 seconds (1 minute) before generating the next PDF
}

// Close the database connection
$conn->close();
?>

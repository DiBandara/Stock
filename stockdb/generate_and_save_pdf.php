<?php
include 'db.php'; // Make sure your db.php file contains the correct database connection

// SQL query to get data from the 'inventory' table
$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);

$inventory = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $inventory[] = $row; // Add each row to the array
    }
} else {
    echo "No records found";
    exit;
}

// Directory to save the PDFs (make sure this folder exists)
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
}

// Get current date for file naming (format: YYYY-MM-DD)
$currentDate = date('Y-m-d');
$fileName = $currentDate . '_inventory_documentation.pdf'; // PDF file name with the current date
$filePath = $uploadDir . $fileName;

// Generate the PDF (using FPDF library or jsPDF for backend server-side PDF generation)
require('fpdf/fpdf.php'); // Make sure to include FPDF or another library for server-side PDF generation

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, 'Inventory Data');

$pdf->Ln(20);
$pdf->SetFont('Arial', '', 12);

// Table Header
$pdf->Cell(20, 10, 'ID', 1);
$pdf->Cell(40, 10, 'Name', 1);
$pdf->Cell(60, 10, 'Description', 1);
$pdf->Cell(30, 10, 'Category', 1);
$pdf->Cell(30, 10, 'SubCategory', 1);
$pdf->Cell(20, 10, 'Cost', 1);
$pdf->Cell(30, 10, 'SupplierID', 1);
$pdf->Cell(20, 10, 'Quantity', 1);
$pdf->Cell(30, 10, 'ItemAddDate', 1);
$pdf->Ln();

// Table Data
foreach ($inventory as $item) {
    $pdf->Cell(20, 10, $item['ID'], 1);
    $pdf->Cell(40, 10, $item['Name'], 1);
    $pdf->Cell(60, 10, $item['Description'], 1);
    $pdf->Cell(30, 10, $item['Category'], 1);
    $pdf->Cell(30, 10, $item['SubCategory'], 1);
    $pdf->Cell(20, 10, $item['Cost'], 1);
    $pdf->Cell(30, 10, $item['SupplierID'], 1);
    $pdf->Cell(20, 10, $item['Quantity'], 1);
    $pdf->Cell(30, 10, $item['ItemAddDate'], 1);
    $pdf->Ln();
}

// Save the PDF to the server
$pdf->Output('F', $filePath);

// Insert PDF details into the saved_pdfs table
$stmt = $conn->prepare("INSERT INTO saved_pdfs (file_name, file_path, generated_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $fileName, $filePath);

if ($stmt->execute()) {
    echo "PDF successfully saved and recorded in the database.";
} else {
    echo "Failed to save PDF information to the database.";
}

$stmt->close();
$conn->close();
?>

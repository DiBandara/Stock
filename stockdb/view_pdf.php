<?php
// Database connection code...
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "stockdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch PDF details from the database based on the file name
function fetchPdfByFileName($fileName) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM saved_pdfs WHERE file_name = ?");
    $stmt->bind_param("s", $fileName);
    $stmt->execute();
    $result = $stmt->get_result();
    $pdf = $result->fetch_assoc();
    return $pdf;
}

if (isset($_GET['file'])) {
    $fileName = $_GET['file'];
    $pdf = fetchPdfByFileName($fileName);
    if (!$pdf) {
        echo "PDF not found.";
        exit;
    }
    $filePath = 'uploads/' . $pdf['file_name'];
} else {
    echo "No file specified.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center">View and Download PDF</h2>

        <div class="text-center">
            <!-- Embed the PDF for viewing -->
            <iframe src="uploads/<?= $fileName ?>" width="100%" height="600px"></iframe>

            <br><br>

            <!-- Download button -->
            <a href="?file=<?= $fileName ?>&download=1" class="btn btn-primary">Download PDF</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

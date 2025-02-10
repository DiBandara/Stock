<?php
// Set the time zone
date_default_timezone_set('Asia/Kolkata');

// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "stockdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure directories exist
if (!file_exists("temp")) {
    mkdir("temp", 0777, true);
}
if (!file_exists("pdfs")) {
    mkdir("pdfs", 0777, true);
}

// Function to save the generated PDF to the database
function save_pdf($file_name, $file_path) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO saved_pdfs (file_name, file_path, generated_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $file_name, $file_path, date('Y-m-d H:i:s'));
    $stmt->execute();
    $stmt->close();
}

// Function to generate and save PDF
function generate_and_save_pdf() {
    global $conn;

    $file_name = "inventory_" . time() . ".pdf";
    $html_file_path = "temp/inventory_" . time() . ".html";
    $pdf_file_path = "pdfs/" . $file_name;

    // HTML Content
    $html_content = "
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
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Item A</td>
                            <td>Description A</td>
                            <td>Category A</td>
                            <td>SubCategory A</td>
                            <td>$100</td>
                            <td>Supplier A</td>
                            <td>10</td>
                            <td>" . date('Y-m-d H:i:s') . "</td>
                        </tr>
                    </tbody>
                </table>
            </body>
        </html>
    ";

    // Save HTML content
    file_put_contents($html_file_path, $html_content);

    // Convert to PDF using wkhtmltopdf
    $cmd = "wkhtmltopdf \"$html_file_path\" \"$pdf_file_path\"";
    exec($cmd, $output, $return_var);

    if ($return_var === 0) {
        save_pdf($file_name, $pdf_file_path);
    } else {
        echo "PDF generation failed. Check wkhtmltopdf installation.";
    }

    unlink($html_file_path);
}

// Generate PDF every minute (if required)
if (date('s') == 0) {
    generate_and_save_pdf();
}

// Fetch saved PDFs
$sql = "SELECT file_name, file_path, generated_at FROM saved_pdfs ORDER BY generated_at DESC";
$result = $conn->query($sql);
$pdfs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdfs[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved PDFs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center">Saved PDFs</h2>
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Generated At</th>
                    <th>Download</th>
                    <th>Show</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pdfs as $pdf): ?>
                    <tr>
                        <td><?= htmlspecialchars($pdf['file_name']) ?></td>
                        <td><?= htmlspecialchars($pdf['generated_at']) ?></td>
                        <td><a href="<?= htmlspecialchars($pdf['file_path']) ?>" class="btn btn-success" download>Download</a></td>
                        <td><button class="btn btn-info show-btn" data-filepath="<?= htmlspecialchars($pdf['file_path']) ?>">Show</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- PDF Display Section -->
        <div id="pdfDisplay" class="mt-5" style="display:none;">
            <h4>PDF Preview</h4>
            <iframe id="pdfViewer" style="width: 100%; height: 500px;" src="" frameborder="0"></iframe>
            <button class="btn btn-secondary" onclick="closePdf()">Close</button>
        </div>
    </div>

    <script>
        document.querySelectorAll('.show-btn').forEach(button => {
            button.addEventListener('click', function() {
                const filePath = this.getAttribute('data-filepath');
                const pdfViewer = document.getElementById('pdfViewer');
                const pdfDisplay = document.getElementById('pdfDisplay');

                pdfViewer.src = filePath;
                pdfDisplay.style.display = 'block';
            });
        });

        function closePdf() {
            document.getElementById('pdfDisplay').style.display = 'none';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

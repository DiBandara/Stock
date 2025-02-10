<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Inventory PDF</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- jsPDF AutoTable plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.26/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.3.0/axios.min.js"></script> <!-- For AJAX requests -->
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center">Inventory Data</h2>
        <a href="inventory.php" class="btn btn-primary">Inventory Management</a>
        <button id="generateBtn" class="btn btn-primary">Generate PDF</button>
        <table class="table table-striped mt-3" id="inventoryTable">
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
            <tbody></tbody>
        </table>
    </div>

    <script>
        // Fetch data from PHP backend
        function fetchInventoryData() {
            axios.get('fetch_inventory.php')
                .then(response => {
                    const data = response.data;
                    if (data.message) {
                        alert(data.message);
                    } else {
                        populateTable(data);
                    }
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                });
        }

        // Populate the table with the fetched inventory data
        function populateTable(data) {
            const tableBody = document.querySelector("#inventoryTable tbody");
            tableBody.innerHTML = ""; // Clear the table first

            data.forEach(item => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${item.ID}</td>
                    <td>${item.Name}</td>
                    <td>${item.Description}</td>
                    <td>${item.Category}</td>
                    <td>${item.SubCategory}</td>
                    <td>${item.Cost}</td>
                    <td>${item.SupplierID}</td>
                    <td>${item.Quantity}</td>
            
                    <td>${item.ItemAddDate}</td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Generate PDF from the table data
        function generatePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.setFontSize(18);
            doc.text("Inventory Table Documentation", 20, 20);

            doc.setFontSize(14);
            doc.text("Table: inventory", 20, 40);

            // Prepare data for the table
            const tableData = [];
            document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
                const rowData = Array.from(row.children).map(cell => cell.textContent);
                tableData.push(rowData);
            });

            // Generate table in the PDF using autoTable
            doc.autoTable({
                startY: 50,
                head: [["ID", "Name", "Description", "Category", "SubCategory", "Cost", "SupplierID", "Quantity", "ItemAddDate"]],
                body: tableData
            });

            // Save the generated PDF
            doc.save('inventory_documentation.pdf');
        }

        // Event listener for the "Generate PDF" button
        document.getElementById("generateBtn").addEventListener("click", function() {
            generatePDF();
        });

        // Fetch and display data on page load
        fetchInventoryData();
    </script>

    <!-- Bootstrap JS (optional for some features like modals) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

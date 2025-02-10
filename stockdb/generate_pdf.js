// Fetch data from the inventory table (from your PHP script)
function fetchInventoryData() {
    axios.get('fetch_inventory.php')
        .then(response => {
            const data = response.data;
            if (data.message) {
                alert(data.message); // Show an alert if there are no records
            } else {
                generatePDF(data); // Pass the fetched data to the generatePDF function
            }
        })
        .catch(error => {
            console.error("Error fetching data:", error);
        });
}

// Generate the PDF from the inventory data
function generatePDF(data) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(18);
    doc.text("Inventory Table Documentation", 20, 20);

    doc.setFontSize(14);
    doc.text("Table: inventory", 20, 40);

    // Prepare data for the table
    const tableData = [];
    data.forEach(item => {
        tableData.push([item.ID, item.Name, item.Description, item.Category, item.SubCategory, item.Cost, item.SupplierID, item.Quantity, item.ItemAddDate]);
    });

    // Generate the table in the PDF using autoTable
    doc.autoTable({
        startY: 50,
        head: [["ID", "Name", "Description", "Category", "SubCategory", "Cost", "SupplierID", "Quantity", "ItemAddDate"]],
        body: tableData
    });

    // Save the PDF to a blob (this can be used for uploading)
    const pdfBlob = doc.output('blob');

    // Create a FormData object and append the PDF Blob
    const formData = new FormData();
    formData.append('pdfFile', pdfBlob, 'inventory_documentation.pdf');

    // Send the PDF to the server to save it
    axios.post('save_pdf.php', formData)
        .then(response => {
            const data = response.data;
            alert(data.message); // Show a success message
        })
        .catch(error => {
            console.error("Error saving PDF:", error);
        });
}

// Event listener for the "Generate PDF" button
document.getElementById("generateBtn").addEventListener("click", function() {
    fetchInventoryData(); // Fetch inventory data and generate the PDF
});

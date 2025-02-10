document.getElementById("generateBtn").addEventListener("click", function() {
    const { jsPDF } = window.jspdf;  // Load jsPDF
    const doc = new jsPDF();  // Create a new PDF document

    // Set the document title
    doc.setFontSize(18);
    doc.text("Database Documentation - Inventory Table", 20, 20);

    // Add the table name
    doc.setFontSize(14);
    doc.text("Table: inventory", 20, 40);

    // Table column headers and data
    const tableData = [
        ["Column Name", "Data Type", "Description", "Constraints"],
        ["ID", "INT", "Unique identifier for each item", "Primary Key, Auto-increment"],
        ["Name", "VARCHAR(255)", "Name of the item", "NOT NULL"],
        ["Description", "TEXT", "Detailed description of the item", "NULL"],
        ["Category", "VARCHAR(100)", "Category the item belongs to", "NULL"],
        ["SubCategory", "VARCHAR(100)", "Sub-category of the item", "NULL"],
        ["Cost", "DECIMAL(10,2)", "Cost price of the item", "NOT NULL"],
        ["SupplierID", "INT", "Identifier for the supplier", "Foreign Key"],
        ["Quantity", "INT", "Stock quantity available", "NOT NULL"],
        ["LastUpdateTime", "TIMESTAMP", "Timestamp of last item update", "NULL"],
        ["ItemAddDate", "DATE", "Date when the item was added", "NOT NULL"]
    ];

    // Create the table in the PDF using jsPDF's autoTable plugin
    doc.autoTable({
        startY: 50, // Table starts after 50 units on the Y-axis
        head: tableData[0], // Column headers
        body: tableData.slice(1) // Row data
    });

    // Save the generated document
    doc.save('inventory_table_documentation.pdf');
});

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs
    $email = $_POST['email'];
    $password = $_POST['password'];
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];

    // Hash the password before saving to the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Database connection
    $conn = new mysqli("localhost", "root", "root", "stockdb");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the SQL query to insert the new admin into the 'admins' table
    $sql = "INSERT INTO admins (Email, password, FullName, Role, IsActive) VALUES (?, ?, ?, ?, 1)";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $email, $hashedPassword, $full_name, $role);

        // Execute the statement
        if ($stmt->execute()) {
            echo "New admin created successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing the query: " . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin</title>
</head>
<body>

<h2>Create Admin Account</h2>

<form method="POST" action="create_admin.php">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br><br>

    <label for="full_name">Full Name:</label>
    <input type="text" id="full_name" name="full_name" required>
    <br><br>

    <label for="role">Role:</label>
    <select id="role" name="role" required>
        <option value="Admin">Admin</option>
        <option value="Manager">Manager</option>
    </select>
    <br><br>

    <button type="submit">Create Admin</button>
</form>

</body>
</html>

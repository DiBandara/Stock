<?php
session_start();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli("localhost", "root", "root", "stockdb");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to check admin credentials
    $sql = "SELECT * FROM admins WHERE Email = ?";
    $stmt = $conn->prepare($sql);

    // Check if the prepare statement was successful
    if ($stmt === false) {
        die("Error preparing the SQL statement: " . $conn->error);
    }

    // Bind parameters and execute the query
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any row is returned
    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Check if the password matches using password_verify
        if (isset($user['password']) && password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['admin'] = $email;
            $_SESSION['admin_id'] = $user['AdminID'];
            $_SESSION['full_name'] = $user['FullName'];

            // Update the LastLogin time in the database
            $updateLoginSql = "UPDATE admins SET LastLogin = NOW() WHERE AdminID = ?";
            $updateStmt = $conn->prepare($updateLoginSql);
            $updateStmt->bind_param("i", $user['AdminID']);
            $updateStmt->execute();

            echo "<script>alert('Login successful!'); window.location.href='home.php';</script>";
        } else {
            // Invalid password
            echo "<script>alert('Invalid password. Please try again.');</script>";
        }
    } else {
        // Email not found
        echo "<script>alert('No account found with this email. Please try again.');</script>";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPRPTA Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ffffff, #fda1a1);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Roboto', sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            margin-bottom: 30px;
            text-align: center;
        }
        .login-header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #4A4A4A;
        }
        .login-header p {
            font-size: 14px;
            color: #999;
        }
        .admin-label {
            font-size: 12px;
            color: #e63946;
            font-weight: bold;
            text-align: center;
        }
        .form-control {
            border: none;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .form-control:focus {
            border: none;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
        .btn-login {
            background-color: #ff6f61;
            border: none;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0px 8px 15px rgba(255, 123, 89, 0.3);
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: #e63946;
            box-shadow: 0px 12px 20px rgba(255, 89, 63, 0.5);
        }
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password a {
            color: #0022ff;
            text-decoration: none;
            font-size: 14px;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h1>SPRPTA Admin Login</h1>
        <p>Only authorized administrators can log in</p>
        <div class="admin-label">Admin Access Only</div>
    </div>
    <form action="login.php" method="post">
        <div class="form-group mb-3">
            <input type="email" class="form-control" name="email" placeholder="Email" required>
        </div>
        <div class="form-group mb-3">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-login w-100">Login</button>
        <div class="forgot-password">
            <a href="#">Forgot your password?</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

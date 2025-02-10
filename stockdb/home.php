<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - SPrpta</title>
    <!-- Correct CDN for Boxicons -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <style>
        /* Reset and global styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
            position: relative; /* Allow positioning of logout button */
        }

        .container {
            text-align: center;
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        h1 {
            font-size: 2.5em;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.1em;
            color: #7f8c8d;
            margin-bottom: 40px;
        }

        .button-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .button-container a {
            text-decoration: none;
            color: #fff;
            padding: 20px 40px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 50px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: block;
        }

        .button-container a.inventory {
            background-color: #27ae60;
        }

        .button-container a.supplier {
            background-color: #2980b9;
        }

        .button-container a.issue {
            background-color: #e74c3c;
        }

        /* Hover effects */
        .button-container a:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        /* Animation for fade-in */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .container {
            animation: fadeIn 1s ease-out;
        }

        /* Logout button styling */
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Logout button -->
    <button class="logout-btn" onclick="window.location.href='logout.php'">
        <i class="bx bx-log-out"></i> <!-- Corrected icon class -->
    </button>

    <div class="container">
        <h1>Welcome to SPrpta</h1>
        <p>Please choose an option below:</p>
        <div class="button-container">
            <a href="inventory.php" class="inventory">Inventory</a>
            <a href="supplier.php" class="supplier">Supplier</a>
            <a href="issued.php" class="issue">Issue</a>
        </div>
    </div>
</body>
</html>

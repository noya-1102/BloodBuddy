<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Blood Donation</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .dashboard-container {
            max-width: 600px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #e63946;
        }
        p {
            font-size: 18px;
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            background: #e63946;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn:hover {
            background: #d62828;
        }
    </style>
</head>
<body>
    <script>
        setTimeout(function() {
            window.location.href = "index.php"; // Redirect to index.html after 5 seconds
        }, 3000);
    </script>

    <div class="dashboard-container">
        <h1>Welcome to Your Dashboard</h1>
        <p>You are logged in as <strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong></p>
        <a href="logout.php" class="btn">Logout</a>
    </div>
</body>
</html>



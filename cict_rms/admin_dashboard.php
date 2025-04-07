<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <div class="container">
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
        <h1 style="color: black;">Welcome Admin, <?php echo $_SESSION['name']; ?></h1>
        
        <div class="tabs" >
            <button class="tablink" onclick="openTab(event, 'ManageInventory')">Manage Inventory</button>
            <button class="tablink" onclick="openTab(event, 'Transactions')">Transactions</button>
            <button class="tablink" onclick="openTab(event, 'Reports')">Reports</button>
            <button class="tablink" onclick="openTab(event, 'ManageUsers')">Manage Users</button>
        </div>

        <!-- Tab Content -->
        <div id="ManageInventory" class="tabcontent">
            <?php include 'manage_inventory.php'; ?>
        </div>

        <div id="Transactions" class="tabcontent">
            <h1 style="color: black;">Transaction History</h1>
            <?php include 'transaction_history.php'; ?>
        </div>

        <div id="Reports" class="tabcontent">
            <h1 style="color: black;">Reports</h1>
            <?php include 'reports.php'; ?>
        </div>

        <div id="ManageUsers" class="tabcontent">
            <?php include 'manage_users.php'; ?>
        </div>
    </div>

    <script>
        function openTab(event, tabName) {
            const tabcontent = document.querySelectorAll(".tabcontent");
            tabcontent.forEach(tab => tab.style.display = "none");

            const tablinks = document.querySelectorAll(".tablink");
            tablinks.forEach(tab => tab.classList.remove("active"));

            document.getElementById(tabName).style.display = "block";
            event.currentTarget.classList.add("active");
        }

        // Open the first tab by default
        document.querySelector(".tablink").click();
    </script>
</body>
</html>
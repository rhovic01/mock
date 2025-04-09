<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'officer') {
    header("Location: login.php");
    exit();
}

// Handle updating item availability
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_availability'])) {
    $item_id = $_POST['item_id'];
    $new_availability = $_POST['new_availability'];

    $sql = "UPDATE inventory SET item_availability = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_availability, $item_id);
    $stmt->execute();
    $stmt->close();
}

// Handle borrowing items
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['borrow_item'])) {
    $item_id = $_POST['item_id'];
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $verified_by = $_SESSION['username'];

    // Insert the borrow transaction
    $sql = "INSERT INTO transactions (item_id, student_id, student_name, transaction_type, verified_by, status) VALUES (?, ?, ?, 'borrowed', ?, 'borrowed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $item_id, $student_id, $student_name, $verified_by);
    $stmt->execute();
    $stmt->close();

    // Update item availability
    $sql = "UPDATE inventory SET item_quantity = item_quantity - 1, item_availability = IF(item_quantity - 1 <= 0, 'Unavailable', 'Available') WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->close();
}

// Handle returning items
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['return_item'])) {
    // Sanitize inputs
    $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_SANITIZE_NUMBER_INT);
    $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
    $verified_by = $_SESSION['username'];

    // Validate inputs
    if (empty($transaction_id) || empty($item_id)) {
        echo "<script>alert('Invalid input data.');</script>";
        exit();
    }

    // Start transaction for atomic operations
    $conn->begin_transaction();

    try {
        // Verify the transaction exists and is still borrowed
        $sql = "SELECT id FROM transactions 
                WHERE id = ? AND status = 'borrowed' 
                LIMIT 1 FOR UPDATE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_assoc();
        $stmt->close();

        if ($transaction) {
            // Update the transaction to 'returned'
            $sql = "UPDATE transactions SET 
                    status = 'returned',
                    transaction_type = 'returned',
                    verified_by = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $verified_by, $transaction_id);
            $stmt->execute();
            $stmt->close();

            // Update item availability and quantity
            $sql = "UPDATE inventory SET 
                    item_quantity = item_quantity + 1, 
                    item_availability = IF(item_quantity + 1 > 0, 'available', 'unavailable') 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            echo "<script>alert('Item successfully returned.'); window.location.href = window.location.href;</script>";
        } else {
            $conn->rollback();
            echo "<script>alert('Transaction not found or already returned.');</script>";
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Return error: " . $e->getMessage());
        echo "<script>alert('Error processing return. Please try again.');</script>";
    }
}

// Fetch all items
$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);
$items = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all transactions
$sql = "SELECT * FROM transactions ORDER BY transaction_date DESC";
$result = $conn->query($sql);
$transactions = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Dashboard</title>
    <link rel="stylesheet" href="officer_dashboard.css">
</head>
<body>
    <div class="container">
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
        <h1 style="color: aliceblue;">Welcome Officer, <?php echo $_SESSION['username']; ?></h1>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tablink" onclick="openTab(event, 'BorrowItem')">Borrow Item</button>
            <button class="tablink" onclick="openTab(event, 'ReturnItem')">Return Item</button>
            <button class="tablink" onclick="openTab(event, 'TransactionHistory')">Transaction History</button>
        </div>

        <!-- Include Tab Content -->
        <?php include 'borrow_item.php'; ?>
        <?php include 'return_item.php'; ?>
        <?php include 'transaction_history.php'; ?>
    </div>

    <script>
        function openTab(event, tabName) {
            // Hide all tab content
            const tabcontent = document.querySelectorAll(".tabcontent");
            tabcontent.forEach(tab => tab.style.display = "none");

            // Remove "active" class from all tab links
            const tablinks = document.querySelectorAll(".tablink");
            tablinks.forEach(tab => tab.classList.remove("active"));

            // Show the current tab content and mark the button as active
            document.getElementById(tabName).style.display = "block";
            event.currentTarget.classList.add("active");
        }

        // Open the first tab by default
        document.querySelector(".tablink").click();
    </script>
</body>
</html>

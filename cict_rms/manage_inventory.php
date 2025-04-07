<?php
require 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle CRUD operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_item'])) {
        // Add new item
        $item_name = trim($_POST['item_name']);
        $item_quantity = (int)$_POST['item_quantity'];
        $item_availability = ($item_quantity > 0) ? 'available' : 'unavailable';

        $sql = "INSERT INTO inventory (item_name, item_quantity, item_availability) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $item_name, $item_quantity, $item_availability);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Item added successfully!";
        } else {
            $_SESSION['error'] = "Error adding item: " . $stmt->error;
        }
        $stmt->close();
        header("Location: admin_dashboard.php");
        exit();
    } elseif (isset($_POST['edit_item'])) {
        // Edit existing item
        $id = (int)$_POST['id'];
        $item_name = trim($_POST['item_name']);
        $item_quantity = (int)$_POST['item_quantity'];
        $item_availability = ($item_quantity > 0) ? 'available' : 'unavailable';

        $sql = "UPDATE inventory SET item_name = ?, item_quantity = ?, item_availability = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisi", $item_name, $item_quantity, $item_availability, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Item updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating item: " . $stmt->error;
        }
        $stmt->close();
        header("Location: admin_dashboard.php");
        exit();
    } elseif (isset($_POST['delete_item'])) {
        // Delete item
        $id = (int)$_POST['id'];

        $sql = "DELETE FROM inventory WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Item deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting item: " . $stmt->error;
        }
        $stmt->close();
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of items
$sql = "SELECT COUNT(*) AS total FROM inventory";
$result = $conn->query($sql);
$totalItems = $result->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $limit);

// Fetch items for the current page
$sql = "SELECT * FROM inventory LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title style=>Manage Inventory</title>
    <style>
        /* Your existing styles */
        body {
            font-family: Arial, sans-serif;
            background-color:rgba(0, 0, 0, 0.6);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #2C2C2C;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        /* Add new alert styles */
        .alert {
            padding: 12px 20px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .close-alert {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
        }
        /* Update form styles */
        .form-container {
            background: #2C2C2C;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-container input {
            padding: 10px;
            margin-right: 10px;
            border: 1px #fff;
            border-radius: 4px;
            width: 200px;
        }
        .form-container button {
            padding: 10px 20px;
            background-color: #6A11CB;;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        /* Update table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #6A11CB;;
            color: white;
        }
        tr:hover {
            background-color:rgba(106, 17, 203, 0.2);
        }
        /* Action buttons */
        .action-btn {
            padding: 6px 12px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .edit-btn {
            background-color: #6A11CB;;
            color: white;
        }
        .edit-btn:hover {
            background-color: #6A11CB;;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        /* Pagination */
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            padding: 8px 16px;
            text-decoration: none;
            color: #6A11CB;;
            border: 1px solid #ddd;
            margin: 0 4px;
        }
        .pagination a.active {
            background-color: #6A11CB;;
            color: white;
            border: 1px solid #6A11CB;;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: black;">Manage Inventory</h1>
        
        <!-- Display messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button class="close-alert">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button class="close-alert">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Add Item Form -->
        <div class="form-container">
            <form method="POST" action=>
                <input type="text" name="item_name" placeholder="Item Name" required>
                <input type="number" name="item_quantity" placeholder="Quantity" min="0" required>
                <button type="submit" name="add_item">Add Item</button>
            </form>
        </div>

        <!-- Inventory Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo $item['item_name']; ?></td>
                        <td><?php echo $item['item_quantity']; ?></td>
                        <td><?php echo $item['item_availability']; ?></td>
                        <td>
                            <!-- Edit Form -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <input type="text" name="item_name" value="<?php echo $item['item_name']; ?>" required>
                                <input type="number" name="item_quantity" value="<?php echo $item['item_quantity']; ?>" min="0" required>
                                <button type="submit" name="edit_item" class="action-btn edit-btn">Edit</button>
                            </form>
                            <!-- Delete Form -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="delete_item" class="action-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Close alert buttons
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
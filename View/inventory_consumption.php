<?php
$successMsg = $errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = 'localhost';
    $db = 'out patient clinic';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $itemName = $_POST['item-name'];
        $quantityRequested = (int) $_POST['quantity'];
        // Get total received for this item
        $stmt = $pdo->prepare('SELECT SUM(Quantity) as total_received FROM inventory_receipts WHERE ItemName = ?');
        $stmt->execute([$itemName]);
        $row = $stmt->fetch();
        $totalReceived = (int) ($row['total_received'] ?? 0);
        // Get total consumed for this item
        $stmt = $pdo->prepare('SELECT SUM(Quantity) as total_consumed FROM inventory_consumption WHERE ItemName = ?');
        $stmt->execute([$itemName]);
        $row = $stmt->fetch();
        $totalConsumed = (int) ($row['total_consumed'] ?? 0);
        $available = $totalReceived - $totalConsumed;
        if ($quantityRequested <= $available) {
            // Insert consumption record
            $stmt = $pdo->prepare('INSERT INTO inventory_consumption (ItemName, Quantity, ConsumeDate, DoctorID, Notes) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $itemName,
                $quantityRequested,
                $_POST['consume-date'],
                $_POST['doctor-id'],
                $_POST['notes']
            ]);
            // FIFO subtract from inventory_receipts
            $remaining = $quantityRequested;
            $stmt = $pdo->prepare('SELECT ID, Quantity FROM inventory_receipts WHERE ItemName = ? AND Quantity > 0 ORDER BY IssueDate ASC, ID ASC');
            $stmt->execute([$itemName]);
            while ($row = $stmt->fetch() and $remaining > 0) {
                $rowQty = (int) $row['Quantity'];
                if ($rowQty >= $remaining) {
                    $newQty = $rowQty - $remaining;
                    $updateStmt = $pdo->prepare('UPDATE inventory_receipts SET Quantity = ? WHERE ID = ?');
                    $updateStmt->execute([$newQty, $row['ID']]);
                    $remaining = 0;
                } else {
                    $updateStmt = $pdo->prepare('UPDATE inventory_receipts SET Quantity = 0 WHERE ID = ?');
                    $updateStmt->execute([$row['ID']]);
                    $remaining -= $rowQty;
                }
            }
            $successMsg = 'Inventory consumption recorded and stock updated successfully!';
        } else {
            $errorMsg = 'Not enough stock for this item. Available: ' . $available . ', Requested: ' . $quantityRequested;
        }
    } catch (PDOException $e) {
        $errorMsg = 'Database error: ' . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Consumption</title>
    <link rel="stylesheet" href="css/inventory.css">
</head>

<body>
    <div class="inventory-container">
        <?php if (!empty($successMsg)): ?>
            <div class="note-bar" style="background:#e6ffed;color:#256029;border:1px solid #b7eb8f;"> <?= $successMsg ?>
            </div>
        <?php elseif (!empty($errorMsg)): ?>
            <div class="note-bar" style="background:#fff1f0;color:#a8071a;border:1px solid #ffa39e;"> <?= $errorMsg ?>
            </div>
        <?php endif; ?>
        <div class="note-bar">Please fill in all fields accurately for inventory consumption records.</div>
        <h1>Inventory Consumption</h1>
        <form class="inventory-form" method="post" action="">
            <div class="form-group">
                <label for="doctor-id">Doctor ID</label>
                <input type="number" id="doctor-id" name="doctor-id" value="">
            </div>
            <div class="form-group">
                <label for="item-name">Item Name</label>
                <input type="text" id="item-name" name="item-name" required value="">
            </div>
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1" required value="">
            </div>
            <div class="form-group">
                <label for="consume-date">Consume Date</label>
                <input type="date" id="consume-date" name="consume-date" required value="">
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2"></textarea>
            </div>
            <button type="submit" class="submit-btn">Record Consumption</button>
        </form>
        <h2>Recent Consumption</h2>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Consume Date</th>
                    <th>Doctor ID</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gloves</td>
                    <td>20</td>
                    <td>2024-06-03</td>
                    <td>101</td>
                    <td>Emergency use</td>
                </tr>
                <tr>
                    <td>Masks</td>
                    <td>30</td>
                    <td>2024-06-04</td>
                    <td>102</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
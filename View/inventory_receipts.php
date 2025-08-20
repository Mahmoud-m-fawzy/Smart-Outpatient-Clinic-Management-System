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
        $stmt = $pdo->prepare('INSERT INTO inventory_receipts (ItemName, Quantity, IssueDate, EndDate, ReceptionID, Notes) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['item-name'],
            $_POST['quantity'],
            $_POST['issue-date'],
            $_POST['end-date'] ?: null,
            $_POST['reception-id'],
            $_POST['notes']
        ]);
        $successMsg = 'Inventory receipt added successfully!';
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
    <title>Inventory Receipts</title>
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
        <div class="note-bar">Please ensure all information is accurate before submitting a new receipt.</div>
        <h1>Inventory Receipts</h1>
        <form class="inventory-form" method="post" action="">
            <div class="form-group">
                <label for="id">ID</label>
                <input type="text" id="id" name="id" required placeholder="Enter Receipt ID" value="" disabled>
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
                <label for="issue-date">Issue Date</label>
                <input type="date" id="issue-date" name="issue-date" required value="">
            </div>
            <div class="form-group">
                <label for="end-date">End Date</label>
                <input type="date" id="end-date" name="end-date" value="">
            </div>
            <div class="form-group">
                <label for="reception-id">Reception ID</label>
                <input type="text" id="reception-id" name="reception-id" required value="">
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2"></textarea>
            </div>
            <button type="submit" class="submit-btn">Add Receipt</button>
        </form>
        <button class="view-btn" onclick="window.open('display_inventory_receipts.php', '_blank')" type="button">View
            Inventory Stock</button>
        <button class="view-btn" onclick="window.open('display_inventory_consumption.php', '_blank')" type="button">View
            Consumption History</button>
        <h2>Recent Receipts</h2>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Issue Date</th>
                    <th>End Date</th>
                    <th>Reception ID</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Gloves</td>
                    <td>100</td>
                    <td>2024-06-01</td>
                    <td>2024-07-01</td>
                    <td>201</td>
                    <td>Late delivery</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Masks</td>
                    <td>200</td>
                    <td>2024-06-02</td>
                    <td>2024-07-02</td>
                    <td>202</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
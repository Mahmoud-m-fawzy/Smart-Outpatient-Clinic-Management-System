<?php
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

$successMsg = $errorMsg = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid ID.');
}

$id = (int) $_GET['id'];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Handle update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare('UPDATE inventory_receipts SET ItemName=?, Quantity=?, IssueDate=?, EndDate=?, ReceptionID=?, Notes=? WHERE ID=?');
        $stmt->execute([
            $_POST['item-name'],
            $_POST['quantity'],
            $_POST['issue-date'],
            $_POST['end-date'] ?: null,
            $_POST['reception-id'],
            $_POST['notes'],
            $id
        ]);
        header('Location: display_inventory_receipts.php');
        exit;
    }
    // Fetch record
    $stmt = $pdo->prepare('SELECT * FROM inventory_receipts WHERE ID=?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        die('Record not found.');
    }
} catch (PDOException $e) {
    die('Database error: ' . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Inventory Receipt</title>
    <link rel="stylesheet" href="css/inventory.css">
</head>

<body>
    <div class="inventory-container">
        <h1>Edit Inventory Receipt</h1>
        <form class="inventory-form" method="post" action="">
            <div class="form-group">
                <label for="id">ID</label>
                <input type="text" id="id" name="id" value="<?= htmlspecialchars($row['ID']) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="item-name">Item Name</label>
                <input type="text" id="item-name" name="item-name" required
                    value="<?= htmlspecialchars($row['ItemName']) ?>">
            </div>
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1" required
                    value="<?= htmlspecialchars($row['Quantity']) ?>">
            </div>
            <div class="form-group">
                <label for="issue-date">Issue Date</label>
                <input type="date" id="issue-date" name="issue-date" required
                    value="<?= htmlspecialchars($row['IssueDate']) ?>">
            </div>
            <div class="form-group">
                <label for="end-date">End Date</label>
                <input type="date" id="end-date" name="end-date" value="<?= htmlspecialchars($row['EndDate']) ?>">
            </div>
            <div class="form-group">
                <label for="reception-id">Reception ID</label>
                <input type="text" id="reception-id" name="reception-id" required
                    value="<?= htmlspecialchars($row['ReceptionID']) ?>">
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2"><?= htmlspecialchars($row['Notes']) ?></textarea>
            </div>
            <button type="submit" class="submit-btn">Save Changes</button>
            <a href="display_inventory_receipts.php" class="back-btn" style="margin-left:10px;">Cancel</a>
        </form>
    </div>
</body>

</html>
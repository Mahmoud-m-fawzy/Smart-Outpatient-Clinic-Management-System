<?php
// Database connection settings (update as needed)
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
    $stmt = $pdo->query('SELECT * FROM inventory_consumption');
    $consumptions = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div style='color:red; padding:20px;'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Inventory Consumption</title>
    <link rel="stylesheet" href="css/inventory.css">
</head>

<body>
    <div class="inventory-container">
        <button class="back-btn" onclick="window.location.href='inventory_receipts.php'" type="button">Back</button>
        <h1>Inventory Consumption</h1>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Consume Date</th>
                    <th>Doctor ID</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($consumptions)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($consumptions as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['ID']) ?></td>
                            <td><?= htmlspecialchars($row['ItemName']) ?></td>
                            <td><?= htmlspecialchars($row['Quantity']) ?></td>
                            <td><?= htmlspecialchars($row['ConsumeDate']) ?></td>
                            <td><?= htmlspecialchars($row['DoctorID']) ?></td>
                            <td><?= htmlspecialchars($row['Notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
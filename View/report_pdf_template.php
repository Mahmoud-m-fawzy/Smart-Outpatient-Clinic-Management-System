<?php
// PDF Template for Reports
$logoPath = 'path/to/your/logo.png'; // Update this path to your logo
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report - <?php echo date('Y-m-d'); ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .header img {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        .report-title {
            color: #2c3e50;
            font-size: 24px;
            margin: 10px 0;
        }
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f6fa;
            border-radius: 5px;
        }
        .report-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            color: #3498db;
            font-size: 16px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #2c3e50;
            color: white;
            text-align: left;
            padding: 8px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 10px;
            color: #7f8c8d;
        }
        .chart-container {
            margin: 20px 0;
            text-align: center;
        }
        .chart-container img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <?php if (file_exists($logoPath)): ?>
            <img src="<?php echo $logoPath; ?>" alt="Logo">
        <?php endif; ?>
        <h1 class="report-title"><?php echo htmlspecialchars($reportData['title'] ?? 'Report'); ?></h1>
        <p>Generated on: <?php echo date('F j, Y, g:i a'); ?></p>
    </div>
    
    <div class="report-info">
        <div>
            <strong>Date Range:</strong> 
            <?php 
            echo htmlspecialchars(date('M j, Y', strtotime($startDate))) . ' to ' . 
                 htmlspecialchars(date('M j, Y', strtotime($endDate)));
            ?>
        </div>
        <div>
            <strong>Report Type:</strong> 
            <?php echo htmlspecialchars(ucfirst($reportType)); ?>
        </div>
    </div>
    
    <?php if (isset($reportData['sections'])): ?>
        <?php foreach ($reportData['sections'] as $section): ?>
            <div class="report-section">
                <h3 class="section-title"><?php echo htmlspecialchars($section['title']); ?></h3>
                
                <?php if (isset($section['table'])): ?>
                    <table>
                        <thead>
                            <tr>
                                <?php foreach ($section['table']['headers'] as $header): ?>
                                    <th><?php echo htmlspecialchars($header); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($section['table']['rows'] as $row): ?>
                                <tr>
                                    <?php foreach ($row as $cell): ?>
                                        <td><?php echo htmlspecialchars($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <?php if (isset($section['chart']) && !empty($section['chart'])): ?>
                    <div class="chart-container">
                        <img src="<?php echo $section['chart']; ?>" alt="Chart">
                    </div>
                <?php endif; ?>
                
                <?php if (isset($section['summary'])): ?>
                    <div class="summary">
                        <?php foreach ($section['summary'] as $item): ?>
                            <p><strong><?php echo htmlspecialchars($item['label']); ?>:</strong> 
                            <?php echo htmlspecialchars($item['value']); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No data available for the selected criteria.</p>
    <?php endif; ?>
    
    <div class="footer">
        <p>Report generated by Healthcare Management System &copy; <?php echo date('Y'); ?></p>
        <p>Page {PAGENO} of {nbpg}</p>
    </div>
</body>
</html>

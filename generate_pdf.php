<?php
session_start();
require_once('config.php');

// Check if user is logged in
if (!isLoggedIn()) {
    exit('Not authorized');
}

// Check if analysis result exists
if (!isset($_SESSION['analysis_result'])) {
    exit('No analysis data available');
}

$analysis = $_SESSION['analysis_result'];

// Create the HTML content
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Face Analysis Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #1e40af; margin-bottom: 10px; }
        .timestamp { color: #666; font-size: 14px; }
        .image-container { text-align: center; margin: 20px 0; }
        .image-container img { max-width: 400px; border-radius: 10px; }
        .attributes { margin-top: 30px; }
        .attribute-row { 
            display: flex;
            justify-content: space-between;
            padding: 10px;
            margin: 5px 0;
            background: #f3f4f6;
            border-radius: 5px;
        }
        .label { color: #1e40af; font-weight: bold; }
        .value { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Face Analysis Report</h1>
            <div class="timestamp">Report Generated: ' . htmlspecialchars($analysis['timestamp']) . '</div>
        </div>
        
        <div class="image-container">
            <img src="' . htmlspecialchars($analysis['image']) . '" alt="Analyzed face">
        </div>
        
        <div class="attributes">';

foreach ($analysis['attributes'] as $label => $value) {
    $html .= '
            <div class="attribute-row">
                <span class="label">' . htmlspecialchars($label) . '</span>
                <span class="value">' . htmlspecialchars($value) . '</span>
            </div>';
}

$html .= '
        </div>
    </div>
</body>
</html>';

// Convert HTML to PDF using browser's print functionality
header('Content-Type: text/html');
echo $html;
?>
<script>
window.onload = function() {
    window.print();
}
</script> 
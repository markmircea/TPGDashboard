<?php
// Initialize session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../includes/config.php';
require_once '../includes/functions.php';

try {
    // Get parameters
    $dateFrom = isset($_GET['dateFrom']) && !empty($_GET['dateFrom']) ? $_GET['dateFrom'] : null;
    $dateTo = isset($_GET['dateTo']) && !empty($_GET['dateTo']) ? $_GET['dateTo'] : null;
    $scriptId = isset($_GET['scriptId']) && !empty($_GET['scriptId']) ? intval($_GET['scriptId']) : null;
    $status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : null;
    
    // Get all filtered results (no limit for export)
    $results = getFilteredResults($dateFrom, $dateTo, $scriptId, $status, 10000, 0);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="script_results_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write CSV header
    fputcsv($output, [
        'Script Name',
        'Script Type',
        'Status',
        'Message',
        'Execution Time (seconds)',
        'Reported At'
    ]);
    
    // Write data rows
    foreach ($results as $result) {
        fputcsv($output, [
            $result['script_name'],
            $result['script_type'],
            $result['status'],
            $result['message'],
            $result['execution_time'],
            $result['reported_at']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Export failed: ' . $e->getMessage();
}
?>

<?php
header('Content-Type: application/json');

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
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 20; // Results per page
    $offset = ($page - 1) * $limit;
    
    $dateFrom = isset($_GET['dateFrom']) && !empty($_GET['dateFrom']) ? $_GET['dateFrom'] : null;
    $dateTo = isset($_GET['dateTo']) && !empty($_GET['dateTo']) ? $_GET['dateTo'] : null;
    $scriptId = isset($_GET['scriptId']) && !empty($_GET['scriptId']) ? intval($_GET['scriptId']) : null;
    $status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : null;
    
    // Get filtered results
    $results = getFilteredResults($dateFrom, $dateTo, $scriptId, $status, $limit, $offset);
    $totalCount = getFilteredResultsCount($dateFrom, $dateTo, $scriptId, $status);
    
    // Calculate pagination
    $totalPages = ceil($totalCount / $limit);
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_results' => $totalCount,
            'results_per_page' => $limit
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>

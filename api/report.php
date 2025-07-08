<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate required fields
if (!$data || !isset($data['script_name']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: script_name, status']);
    exit;
}

// Validate status
if (!in_array($data['status'], ['success', 'failure', 'warning', 'info'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Status must be one of: success, failure, warning, info']);
    exit;
}

try {
    // Extract data with defaults
    $scriptName = trim($data['script_name']);
    $scriptType = isset($data['script_type']) ? trim($data['script_type']) : 'general';
    $status = $data['status'];
    $message = isset($data['message']) ? trim($data['message']) : '';
    $detailedMessage = isset($data['detailed_message']) ? trim($data['detailed_message']) : '';
    $executionTime = isset($data['execution_time']) ? floatval($data['execution_time']) : null;
    $description = isset($data['description']) ? trim($data['description']) : '';
    
    // Validate script name
    if (empty($scriptName)) {
        http_response_code(400);
        echo json_encode(['error' => 'Script name cannot be empty']);
        exit;
    }
    
    // Get or create script
    $scriptId = getOrCreateScript($scriptName, $scriptType, $description);
    
    // Record the result
    $resultId = recordScriptResult($scriptId, $status, $message, $detailedMessage, $executionTime);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Script result recorded successfully',
        'result_id' => $resultId,
        'script_id' => $scriptId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>

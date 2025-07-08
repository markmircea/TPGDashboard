<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Read the README.md file
    $readmePath = '../README.md';
    
    if (!file_exists($readmePath)) {
        throw new Exception('README.md file not found');
    }
    
    $readmeContent = file_get_contents($readmePath);
    
    if ($readmeContent === false) {
        throw new Exception('Unable to read README.md file');
    }
    
    // Return the content with timestamp for debugging
    echo json_encode([
        'success' => true,
        'content' => $readmeContent,
        'last_modified' => filemtime($readmePath),
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

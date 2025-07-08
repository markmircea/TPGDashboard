<?php
/**
 * Sample test script to demonstrate how to report script results to the dashboard
 * This script simulates an SFTP download/upload operation
 */

// Dashboard API endpoint
$apiUrl = 'http://localhost:8000/api/report.php'; // Adjust this URL as needed

// Function to report script result to dashboard
function reportToDashboard($scriptName, $scriptType, $status, $message, $executionTime = null, $description = '') {
    global $apiUrl;
    
    $data = [
        'script_name' => $scriptName,
        'script_type' => $scriptType,
        'status' => $status,
        'message' => $message,
        'execution_time' => $executionTime,
        'description' => $description
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($apiUrl, false, $context);
    
    if ($result === FALSE) {
        echo "Failed to report to dashboard\n";
    } else {
        $response = json_decode($result, true);
        if ($response && $response['success']) {
            echo "Successfully reported to dashboard\n";
        } else {
            echo "Dashboard reported error: " . ($response['error'] ?? 'Unknown error') . "\n";
        }
    }
}

// Simulate script execution
echo "Starting SFTP Download Script...\n";
$startTime = microtime(true);

try {
    // Simulate some work
    echo "Connecting to SFTP server...\n";
    sleep(1); // Simulate connection time
    
    echo "Downloading files...\n";
    sleep(2); // Simulate download time
    
    // Simulate random success/failure
    $success = rand(1, 10) > 2; // 80% success rate
    
    if ($success) {
        $filesDownloaded = rand(5, 25);
        $message = "Successfully downloaded {$filesDownloaded} files from SFTP server";
        $status = 'success';
        echo $message . "\n";
    } else {
        $message = "Failed to download files: Connection timeout";
        $status = 'failure';
        echo $message . "\n";
    }
    
} catch (Exception $e) {
    $message = "Script failed with exception: " . $e->getMessage();
    $status = 'failure';
    echo $message . "\n";
}

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

echo "Script completed in {$executionTime} seconds\n";

// Report to dashboard
reportToDashboard(
    'sftp_download_script',
    'file_transfer',
    $status,
    $message,
    $executionTime,
    'Downloads files from SFTP server and processes them'
);

echo "Test script finished.\n";
?>

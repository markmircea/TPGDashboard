<?php
/**
 * Sample test script to demonstrate how to report script results to the dashboard
 * This script simulates an SFTP download/upload operation
 */

// Dashboard API endpoint
$apiUrl = 'http://localhost:8000/api/report.php'; // Adjust this URL as needed

// Function to report script result to dashboard
function reportToDashboard($scriptName, $scriptType, $status, $message, $executionTime = null, $description = '', $detailedMessage = '') {
    global $apiUrl;
    
    $data = [
        'script_name' => $scriptName,
        'script_type' => $scriptType,
        'status' => $status,
        'message' => $message,
        'execution_time' => $executionTime,
        'description' => $description,
        'detailed_message' => $detailedMessage
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

// Create detailed message with technical information
$detailedMessage = "Script Execution Details:\n\n";
$detailedMessage .= "Server: sftp.example.com:22\n";
$detailedMessage .= "Connection Method: SSH Key Authentication\n";
$detailedMessage .= "Remote Directory: /incoming/files/\n";
$detailedMessage .= "Local Directory: ./downloads/\n\n";

if ($success) {
    $detailedMessage .= "Files Downloaded:\n";
    for ($i = 1; $i <= $filesDownloaded; $i++) {
        $detailedMessage .= "- file_{$i}.csv (Size: " . rand(1024, 10240) . " bytes)\n";
    }
    $detailedMessage .= "\nTotal Transfer Size: " . number_format($filesDownloaded * 5000) . " bytes\n";
    $detailedMessage .= "Average Transfer Speed: " . rand(500, 2000) . " KB/s\n";
} else {
    $detailedMessage .= "Error Details:\n";
    $detailedMessage .= "- Connection timeout after 30 seconds\n";
    $detailedMessage .= "- Retry attempts: 3\n";
    $detailedMessage .= "- Last successful connection: " . date('Y-m-d H:i:s', time() - rand(3600, 86400)) . "\n";
    $detailedMessage .= "- Network latency: " . rand(100, 500) . "ms\n";
}

$detailedMessage .= "\nSystem Information:\n";
$detailedMessage .= "- PHP Version: " . PHP_VERSION . "\n";
$detailedMessage .= "- Memory Usage: " . number_format(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
$detailedMessage .= "- Peak Memory: " . number_format(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";

// Report to dashboard
reportToDashboard(
    'sftp_download_script',
    'file_transfer',
    $status,
    $message,
    $executionTime,
    'Downloads files from SFTP server and processes them',
    $detailedMessage
);

echo "Test script finished.\n";
?>

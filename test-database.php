<?php
/**
 * Test script to verify database functionality without requiring the web server
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

echo "Testing database functionality...\n\n";

try {
    // Test 1: Create a test script
    echo "1. Creating test script...\n";
    $scriptId = getOrCreateScript('test_script', 'testing', 'A test script for verification');
    echo "   Script ID: $scriptId\n";
    
    // Test 2: Record some results
    echo "2. Recording test results...\n";
    recordScriptResult($scriptId, 'success', 'Test completed successfully', 2.5);
    recordScriptResult($scriptId, 'failure', 'Test failed due to timeout', 1.2);
    recordScriptResult($scriptId, 'success', 'Test completed successfully again', 3.1);
    echo "   Recorded 3 test results\n";
    
    // Test 3: Get statistics
    echo "3. Getting statistics...\n";
    $stats = getScriptStatistics();
    echo "   Total scripts: " . $stats['total_scripts'] . "\n";
    echo "   Total executions: " . $stats['total_executions'] . "\n";
    echo "   Success rate: " . $stats['success_rate'] . "%\n";
    
    // Test 4: Get recent results
    echo "4. Getting recent results...\n";
    $recent = getRecentResults(5);
    echo "   Found " . count($recent) . " recent results\n";
    
    // Test 5: Get all scripts
    echo "5. Getting all scripts...\n";
    $scripts = getAllScripts();
    echo "   Found " . count($scripts) . " scripts\n";
    
    echo "\n✅ All database tests passed successfully!\n";
    echo "The dashboard should now display data when you access it via web browser.\n";
    
} catch (Exception $e) {
    echo "\n❌ Database test failed: " . $e->getMessage() . "\n";
    echo "Please check your MySQL connection and database setup.\n";
}
?>

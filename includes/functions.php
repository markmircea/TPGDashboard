<?php
require_once 'config.php';

// Get or create script ID
function getOrCreateScript($scriptName, $scriptType, $description = '') {
    $pdo = getDatabase();
    
    // Check if script exists
    $stmt = $pdo->prepare("SELECT id FROM scripts WHERE script_name = ?");
    $stmt->execute([$scriptName]);
    $script = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($script) {
        return $script['id'];
    }
    
    // Create new script
    $stmt = $pdo->prepare("INSERT INTO scripts (script_name, script_type, description) VALUES (?, ?, ?)");
    $stmt->execute([$scriptName, $scriptType, $description]);
    
    return $pdo->lastInsertId();
}

// Record script result
function recordScriptResult($scriptId, $status, $message = '', $detailedMessage = '', $executionTime = null) {
    $pdo = getDatabase();
    
    $stmt = $pdo->prepare("INSERT INTO script_results (script_id, status, message, detailed_message, execution_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$scriptId, $status, $message, $detailedMessage, $executionTime]);
    
    return $pdo->lastInsertId();
}

// Get recent script results
function getRecentResults($limit = 20) {
    $pdo = getDatabase();
    
    $stmt = $pdo->prepare("
        SELECT sr.*, s.script_name, s.script_type 
        FROM script_results sr 
        JOIN scripts s ON sr.script_id = s.id 
        ORDER BY sr.reported_at DESC 
        LIMIT " . intval($limit)
    );
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get script statistics (Today only)
function getScriptStatistics() {
    $pdo = getDatabase();
    
    // Get today's date in the format the database expects
    $today = date('Y-m-d');
    
    // Total executions for today
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM script_results WHERE DATE(reported_at) = ?");
    $stmt->execute([$today]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Success count for today
    $stmt = $pdo->prepare("SELECT COUNT(*) as success FROM script_results WHERE status = 'success' AND DATE(reported_at) = ?");
    $stmt->execute([$today]);
    $success = $stmt->fetch(PDO::FETCH_ASSOC)['success'];
    
    // Failure count for today
    $stmt = $pdo->prepare("SELECT COUNT(*) as failure FROM script_results WHERE status = 'failure' AND DATE(reported_at) = ?");
    $stmt->execute([$today]);
    $failure = $stmt->fetch(PDO::FETCH_ASSOC)['failure'];
    
    // Scripts that ran today (unique scripts)
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT script_id) as scripts_today FROM script_results WHERE DATE(reported_at) = ?");
    $stmt->execute([$today]);
    $scriptsToday = $stmt->fetch(PDO::FETCH_ASSOC)['scripts_today'];
    
    // Success rate for today
    $successRate = $total > 0 ? round(($success / $total) * 100, 2) : 0;
    
    // Scripts count (total scripts - this remains the same as it's not time-dependent)
    $stmt = $pdo->query("SELECT COUNT(*) as scripts FROM scripts");
    $scriptsCount = $stmt->fetch(PDO::FETCH_ASSOC)['scripts'];
    
    return [
        'total_executions' => $total,
        'successful_executions' => $success,
        'failed_executions' => $failure,
        'success_rate' => $successRate,
        'total_scripts' => $scriptsCount,
        'scripts_run_today' => $scriptsToday
    ];
}

// Get all scripts
function getAllScripts() {
    $pdo = getDatabase();
    
    $stmt = $pdo->query("
        SELECT s.*, 
               COUNT(sr.id) as total_executions,
               SUM(CASE WHEN sr.status = 'success' THEN 1 ELSE 0 END) as successful_executions,
               MAX(sr.reported_at) as last_execution
        FROM scripts s 
        LEFT JOIN script_results sr ON s.id = sr.script_id 
        GROUP BY s.id 
        ORDER BY s.script_name
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get filtered results for historical analysis
function getFilteredResults($dateFrom = null, $dateTo = null, $scriptId = null, $status = null, $limit = 100, $offset = 0) {
    $pdo = getDatabase();
    
    $conditions = [];
    $params = [];
    
    if ($dateFrom) {
        $conditions[] = "sr.reported_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if ($dateTo) {
        $conditions[] = "sr.reported_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    if ($scriptId) {
        $conditions[] = "sr.script_id = ?";
        $params[] = $scriptId;
    }
    
    if ($status && $status !== 'all') {
        $conditions[] = "sr.status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    $sql = "
        SELECT sr.*, s.script_name, s.script_type 
        FROM script_results sr 
        JOIN scripts s ON sr.script_id = s.id 
        $whereClause
        ORDER BY sr.reported_at DESC 
        LIMIT " . intval($limit) . " OFFSET " . intval($offset);
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get count for filtered results
function getFilteredResultsCount($dateFrom = null, $dateTo = null, $scriptId = null, $status = null) {
    $pdo = getDatabase();
    
    $conditions = [];
    $params = [];
    
    if ($dateFrom) {
        $conditions[] = "sr.reported_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if ($dateTo) {
        $conditions[] = "sr.reported_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    if ($scriptId) {
        $conditions[] = "sr.script_id = ?";
        $params[] = $scriptId;
    }
    
    if ($status && $status !== 'all') {
        $conditions[] = "sr.status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    $sql = "SELECT COUNT(*) as total FROM script_results sr JOIN scripts s ON sr.script_id = s.id $whereClause";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Format date for display
function formatDate($date) {
    return date('Y-m-d H:i:s', strtotime($date));
}

// Format execution time
function formatExecutionTime($time) {
    if ($time === null) return 'N/A';
    return number_format($time, 2) . 's';
}

// Escape HTML output
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Get scripts that ran today with their statistics
function getTodaysScripts() {
    $pdo = getDatabase();
    
    // Get today's date
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT 
            s.script_name,
            s.script_type,
            COUNT(sr.id) as total_runs_today,
            SUM(CASE WHEN sr.status = 'success' THEN 1 ELSE 0 END) as successful_runs_today,
            ROUND(
                CASE 
                    WHEN COUNT(sr.id) > 0 
                    THEN (SUM(CASE WHEN sr.status = 'success' THEN 1 ELSE 0 END) / COUNT(sr.id)) * 100 
                    ELSE 0 
                END, 2
            ) as success_rate_today,
            (
                SELECT sr2.status 
                FROM script_results sr2 
                WHERE sr2.script_id = s.id 
                  AND DATE(sr2.reported_at) = ? 
                ORDER BY sr2.reported_at DESC 
                LIMIT 1
            ) as last_status,
            (
                SELECT sr3.message 
                FROM script_results sr3 
                WHERE sr3.script_id = s.id 
                  AND DATE(sr3.reported_at) = ? 
                ORDER BY sr3.reported_at DESC 
                LIMIT 1
            ) as last_message,
            (
                SELECT sr4.detailed_message 
                FROM script_results sr4 
                WHERE sr4.script_id = s.id 
                  AND DATE(sr4.reported_at) = ? 
                ORDER BY sr4.reported_at DESC 
                LIMIT 1
            ) as last_detailed_message,
            (
                SELECT sr5.execution_time 
                FROM script_results sr5 
                WHERE sr5.script_id = s.id 
                  AND DATE(sr5.reported_at) = ? 
                ORDER BY sr5.reported_at DESC 
                LIMIT 1
            ) as last_execution_time,
            (
                SELECT sr6.reported_at 
                FROM script_results sr6 
                WHERE sr6.script_id = s.id 
                  AND DATE(sr6.reported_at) = ? 
                ORDER BY sr6.reported_at DESC 
                LIMIT 1
            ) as last_execution
        FROM scripts s
        INNER JOIN script_results sr ON s.id = sr.script_id
        WHERE DATE(sr.reported_at) = ?
        GROUP BY s.id, s.script_name, s.script_type
        ORDER BY last_execution DESC, total_runs_today DESC, s.script_name
    ");
    
    $stmt->execute([$today, $today, $today, $today, $today, $today]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
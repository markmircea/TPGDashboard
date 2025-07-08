<?php
// Initialize session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

define('APP_NAME', 'TPG Dashboard');

// Load required files
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get dashboard data
$stats = getScriptStatistics();
$recentResults = getRecentResults(10);
$allScripts = getAllScripts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="dashboard-page">
    <header class="header">
        <div class="header-content">
            <h1><?php echo APP_NAME; ?></h1>
            <div class="user-info">
                <span>Welcome, <?php echo escape($_SESSION['username']); ?></span>
                <a href="?logout" class="btn btn-secondary btn-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <h3>Total Scripts</h3>
                <div class="stat-value"><?php echo $stats['total_scripts']; ?></div>
            </div>
            <div class="stat-card info">
                <h3>Total Executions</h3>
                <div class="stat-value"><?php echo $stats['total_executions']; ?></div>
            </div>
            <div class="stat-card success">
                <h3>Success Rate</h3>
                <div class="stat-value"><?php echo $stats['success_rate']; ?>%</div>
            </div>
            <div class="stat-card danger">
                <h3>Failed Executions</h3>
                <div class="stat-value"><?php echo $stats['failed_executions']; ?></div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showSection('overview')">Overview</button>
            <button class="nav-tab" onclick="showSection('scripts')">Scripts</button>
            <button class="nav-tab" onclick="showSection('history')">History</button>
        </div>

        <!-- Overview Section -->
        <div id="overview" class="content-section active">
            <div class="table-container">
                <div class="table-header">
                    <h2>Recent Activity</h2>
                    <button class="btn btn-primary btn-sm" onclick="refreshData()">Refresh</button>
                </div>
                <div id="recent-results">
                    <?php if (empty($recentResults)): ?>
                        <div class="empty-state">
                            <h3>No script results yet</h3>
                            <p>Scripts will appear here once they start reporting their status.</p>
                        </div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Script Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Message</th>
                                    <th>Execution Time</th>
                                    <th>Reported At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentResults as $result): ?>
                                    <tr>
                                        <td><?php echo escape($result['script_name']); ?></td>
                                        <td><?php echo escape($result['script_type']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $result['status']; ?>">
                                                <?php echo escape($result['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo escape($result['message']); ?></td>
                                        <td><?php echo formatExecutionTime($result['execution_time']); ?></td>
                                        <td><?php echo formatDate($result['reported_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Scripts Section -->
        <div id="scripts" class="content-section">
            <div class="table-container">
                <div class="table-header">
                    <h2>All Scripts</h2>
                </div>
                <?php if (empty($allScripts)): ?>
                    <div class="empty-state">
                        <h3>No scripts registered</h3>
                        <p>Scripts will be automatically registered when they first report their status.</p>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Script Name</th>
                                <th>Type</th>
                                <th>Total Executions</th>
                                <th>Successful</th>
                                <th>Success Rate</th>
                                <th>Last Execution</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allScripts as $script): ?>
                                <?php 
                                $successRate = $script['total_executions'] > 0 
                                    ? round(($script['successful_executions'] / $script['total_executions']) * 100, 2) 
                                    : 0;
                                ?>
                                <tr>
                                    <td><?php echo escape($script['script_name']); ?></td>
                                    <td><?php echo escape($script['script_type']); ?></td>
                                    <td><?php echo $script['total_executions']; ?></td>
                                    <td><?php echo $script['successful_executions']; ?></td>
                                    <td><?php echo $successRate; ?>%</td>
                                    <td><?php echo $script['last_execution'] ? formatDate($script['last_execution']) : 'Never'; ?></td>
                                    <td><?php echo formatDate($script['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- History Section -->
        <div id="history" class="content-section">
            <div class="filters">
                <h3>Filter Results</h3>
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="date-from">From Date</label>
                        <input type="date" id="date-from" name="date-from">
                    </div>
                    <div class="filter-group">
                        <label for="date-to">To Date</label>
                        <input type="date" id="date-to" name="date-to">
                    </div>
                    <div class="filter-group">
                        <label for="script-filter">Script</label>
                        <select id="script-filter" name="script-filter">
                            <option value="">All Scripts</option>
                            <?php foreach ($allScripts as $script): ?>
                                <option value="<?php echo $script['id']; ?>">
                                    <?php echo escape($script['script_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="status-filter">Status</label>
                        <select id="status-filter" name="status-filter">
                            <option value="all">All</option>
                            <option value="success">Success</option>
                            <option value="failure">Failure</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Historical Results</h2>
                    <button class="btn btn-secondary btn-sm" onclick="exportResults()">Export CSV</button>
                </div>
                <div id="history-results">
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </div>
                <div id="pagination" class="pagination" style="display: none;"></div>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
</body>
</html>

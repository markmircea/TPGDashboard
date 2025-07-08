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
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-page">
    <header class="header">
        <div class="header-content">
            <h1>
                <i class="fas fa-tachometer-alt"></i>
                <?php echo APP_NAME; ?>
            </h1>
            <div class="user-info">
                <button class="btn btn-info btn-sm" onclick="showApiInstructions()">
                    <i class="fas fa-code"></i>
                    API Instructions
                </button>
                <span>
                    <i class="fas fa-user"></i>
                    Welcome, <?php echo escape($_SESSION['username']); ?>
                </span>
                <a href="?logout" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Enhanced Statistics Cards -->
        <div class="stats-grid fade-in">
            <div class="stat-card primary">
                <div class="stat-card-header">
                    <h3>Scripts Run Today</h3>
                    <div class="stat-card-icon">
                        <i class="fas fa-file-code"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['scripts_run_today']; ?></div>
                <div class="stat-trend">
                    <i class="fas fa-calendar-day"></i>
                    Unique scripts executed
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-card-header">
                    <h3>Executions Today</h3>
                    <div class="stat-card-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['total_executions']; ?></div>
                <div class="stat-trend">
                    <i class="fas fa-calendar-day"></i>
                    Since midnight
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-card-header">
                    <h3>Success Rate</h3>
                    <div class="stat-card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['success_rate']; ?>%</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                    Today's performance
                </div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-card-header">
                    <h3>Failed Executions</h3>
                    <div class="stat-card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['failed_executions']; ?></div>
                <div class="stat-trend">
                    <i class="fas fa-calendar-day"></i>
                    Needs attention
                </div>
            </div>
        </div>

        <!-- Enhanced Navigation Tabs -->
        <div class="nav-tabs fade-in">
            <button class="nav-tab active" onclick="showSection('overview')">
                <i class="fas fa-chart-pie"></i>
                Overview
            </button>
            <button class="nav-tab" onclick="showSection('scripts')">
                <i class="fas fa-cogs"></i>
                Scripts
            </button>
            <button class="nav-tab" onclick="showSection('history')">
                <i class="fas fa-history"></i>
                History
            </button>
        </div>

        <!-- Overview Section -->
        <div id="overview" class="content-section active fade-in">
            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-activity"></i>
                        Recent Activity
                    </h2>
                    <button class="btn btn-primary btn-sm" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>
                <div id="recent-results">
                    <?php if (empty($recentResults)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No script results yet</h3>
                            <p>Scripts will appear here once they start reporting their status.</p>
                        </div>
                    <?php else: ?>
                        <table class="table sortable-table">
                            <thead>
                                <tr>
                                    <th class="sortable" data-column="script_name">
                                        <i class="fas fa-file-alt"></i> Script Name
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th class="sortable" data-column="script_type">
                                        <i class="fas fa-tag"></i> Type
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th class="sortable" data-column="status">
                                        <i class="fas fa-traffic-light"></i> Status
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th class="sortable" data-column="message">
                                        <i class="fas fa-comment"></i> Message
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th class="sortable" data-column="execution_time">
                                        <i class="fas fa-stopwatch"></i> Execution Time
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                    <th class="sortable" data-column="reported_at">
                                        <i class="fas fa-clock"></i> Reported At
                                        <i class="fas fa-sort sort-icon"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentResults as $result): ?>
                                    <tr>
                                        <td><?php echo escape($result['script_name']); ?></td>
                                        <td><?php echo escape($result['script_type']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $result['status']; ?>">
                                                <?php 
                                                $statusIcons = [
                                                    'success' => 'fas fa-check',
                                                    'failure' => 'fas fa-times',
                                                    'warning' => 'fas fa-exclamation',
                                                    'info' => 'fas fa-info'
                                                ];
                                                ?>
                                                <i class="<?php echo $statusIcons[$result['status']] ?? 'fas fa-question'; ?>"></i>
                                                <?php echo escape($result['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo escape($result['message']); ?>
                                            <?php if (!empty($result['detailed_message'])): ?>
                                                <br><button class="btn btn-sm btn-secondary" data-detailed-message="<?php echo htmlspecialchars($result['detailed_message'], ENT_QUOTES, 'UTF-8'); ?>" onclick="showDetailedMessageFromButton(this)">
                                                    <i class="fas fa-eye"></i>
                                                    View Details
                                                </button>
                                            <?php endif; ?>
                                        </td>
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
                    <h2>
                        <i class="fas fa-list"></i>
                        All Scripts
                    </h2>
                </div>
                <?php if (empty($allScripts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-code"></i>
                        <h3>No scripts registered</h3>
                        <p>Scripts will be automatically registered when they first report their status.</p>
                    </div>
                <?php else: ?>
                    <table class="table sortable-table">
                        <thead>
                            <tr>
                                <th class="sortable" data-column="script_name">
                                    <i class="fas fa-file-alt"></i> Script Name
                                    <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th class="sortable" data-column="script_type">
                                    <i class="fas fa-tag"></i> Type
                                    <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th class="sortable" data-column="total_executions">
                                    <i class="fas fa-play"></i> Total Executions
                                    <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th class="sortable" data-column="successful_executions">
                                    <i class="fas fa-check"></i> Successful
                                    <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th class="sortable" data-column="success_rate">
                                    <i class="fas fa-percentage"></i> Success Rate
                                    <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th class="sortable" data-column="last_execution">
                                    <i class="fas fa-clock"></i> Last Execution
                                    <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th class="sortable" data-column="created_at">
                                    <i class="fas fa-calendar-plus"></i> Created
                                    <i class="fas fa-sort sort-icon"></i>
                                </th>
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
                                    <td>
                                        <span class="status-badge <?php echo $successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger'); ?>">
                                            <i class="fas fa-chart-line"></i>
                                            <?php echo $successRate; ?>%
                                        </span>
                                    </td>
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
                <h3>
                    <i class="fas fa-filter"></i>
                    Filter Results
                </h3>
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="date-from">
                            <i class="fas fa-calendar-alt"></i>
                            From Date
                        </label>
                        <input type="date" id="date-from" name="date-from">
                    </div>
                    <div class="filter-group">
                        <label for="date-to">
                            <i class="fas fa-calendar-alt"></i>
                            To Date
                        </label>
                        <input type="date" id="date-to" name="date-to">
                    </div>
                    <div class="filter-group">
                        <label for="script-filter">
                            <i class="fas fa-file-code"></i>
                            Script
                        </label>
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
                        <label for="status-filter">
                            <i class="fas fa-traffic-light"></i>
                            Status
                        </label>
                        <select id="status-filter" name="status-filter">
                            <option value="all">All</option>
                            <option value="success">Success</option>
                            <option value="failure">Failure</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-search"></i>
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-archive"></i>
                        Historical Results
                    </h2>
                    <button class="btn btn-secondary btn-sm" onclick="exportResults()">
                        <i class="fas fa-download"></i>
                        Export CSV
                    </button>
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
    <br>
    <br>

    <!-- Enhanced Modal for detailed messages -->
    <div id="detailModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-info-circle"></i>
                    Detailed Message
                </h3>
                <span class="close" onclick="closeDetailModal()">&times;</span>
            </div>
            <div class="modal-body">
                <pre id="detailText"></pre>
            </div>
        </div>
    </div>

    <!-- Enhanced Modal for API Instructions -->
    <div id="apiModal" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-code"></i>
                    API Instructions
                </h3>
                <span class="close" onclick="closeApiModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="apiInstructions"></div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>
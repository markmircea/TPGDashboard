// Dashboard JavaScript functionality
let currentPage = 1;
let currentFilters = {};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced initialization
    initializeDashboard();
    setupEnhancedFeatures();
    startAutoRefresh();
});

// Enhanced initialization function
function initializeDashboard() {
    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    document.getElementById('date-to').value = formatDateForInput(today);
    document.getElementById('date-from').value = formatDateForInput(thirtyDaysAgo);
    
    // Load initial history data
    loadHistoryData();
}

// Setup enhanced UI features
function setupEnhancedFeatures() {
    // Add fade-in animation to elements as they appear
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    // Observe all content sections
    document.querySelectorAll('.content-section, .table-container, .filters').forEach(el => {
        observer.observe(el);
    });

    // Add pulse animation to important stats
    const criticalStats = document.querySelectorAll('.stat-card.danger .stat-value');
    criticalStats.forEach(stat => {
        if (parseInt(stat.textContent) > 0) {
            stat.classList.add('pulse');
        }
    });

    // Enhanced button hover effects
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Enhanced status badge animations
    document.querySelectorAll('.status-badge').forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Progressive enhancement for tables
    document.querySelectorAll('.table tbody tr').forEach(row => {
        row.addEventListener('click', function() {
            // Add subtle highlight effect
            this.style.backgroundColor = 'rgba(102, 126, 234, 0.05)';
            setTimeout(() => {
                this.style.backgroundColor = '';
            }, 300);
        });
    });

    // Setup keyboard shortcuts
    setupKeyboardShortcuts();
    
    // Initialize sortable tables
    initializeSortableTables();
}

// Initialize sortable tables
function initializeSortableTables() {
    document.querySelectorAll('.sortable-table').forEach(table => {
        const headers = table.querySelectorAll('th.sortable');
        
        headers.forEach(header => {
            header.addEventListener('click', function() {
                sortTable(table, this);
            });
        });
    });
}

// Sort table by column
function sortTable(table, header) {
    const column = header.dataset.column;
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Determine sort direction
    let direction = 'asc';
    if (header.classList.contains('sort-asc')) {
        direction = 'desc';
    }
    
    // Clear all sort classes
    table.querySelectorAll('th.sortable').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });
    
    // Add sort class to current header
    header.classList.add(`sort-${direction}`);
    
    // Get column index
    const columnIndex = Array.from(header.parentNode.children).indexOf(header);
    
    // Sort rows
    rows.sort((a, b) => {
        const aCell = a.cells[columnIndex];
        const bCell = b.cells[columnIndex];
        
        let aValue = getCellValue(aCell, column);
        let bValue = getCellValue(bCell, column);
        
        // Handle different data types
        if (isNumeric(aValue) && isNumeric(bValue)) {
            aValue = parseFloat(aValue);
            bValue = parseFloat(bValue);
        } else if (isDate(aValue) && isDate(bValue)) {
            aValue = new Date(aValue).getTime();
            bValue = new Date(bValue).getTime();
        } else {
            aValue = aValue.toLowerCase();
            bValue = bValue.toLowerCase();
        }
        
        if (direction === 'asc') {
            return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
        } else {
            return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
        }
    });
    
    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
    
    // Add visual feedback
    rows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.01}s`;
        row.classList.add('fade-in');
    });
}

// Get cell value for sorting
function getCellValue(cell, column) {
    // Handle status badges
    const statusBadge = cell.querySelector('.status-badge');
    if (statusBadge) {
        return statusBadge.textContent.trim();
    }
    
    // Handle execution time (remove 's' suffix)
    if (column === 'execution_time') {
        const text = cell.textContent.trim();
        return text.replace('s', '');
    }
    
    // Handle success rate (remove '%' suffix)
    if (column === 'success_rate') {
        const text = cell.textContent.trim();
        return text.replace('%', '');
    }
    
    return cell.textContent.trim();
}

// Check if value is numeric
function isNumeric(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
}

// Check if value is a date
function isDate(value) {
    return !isNaN(Date.parse(value));
}

// Enhanced keyboard shortcuts
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(event) {
        // ESC to close modals
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        modal.style.display = 'none';
                    }, 300);
                }
            });
        }
        
        // Number keys to switch tabs
        if (event.key >= '1' && event.key <= '3') {
            const tabs = ['overview', 'scripts', 'history'];
            const tabIndex = parseInt(event.key) - 1;
            if (tabs[tabIndex]) {
                const tabButton = document.querySelector(`[onclick="showSection('${tabs[tabIndex]}')"]`);
                if (tabButton) {
                    tabButton.click();
                }
            }
        }
    });
}

// Enhanced tab switching with animations
function showSection(sectionName) {
    // Remove active class from all tabs and sections
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
    });
    
    // Add active class to clicked tab
    event.target.classList.add('active');
    
    // Show the corresponding section with animation
    const targetSection = document.getElementById(sectionName);
    if (targetSection) {
        setTimeout(() => {
            targetSection.classList.add('active');
            targetSection.style.opacity = '1';
            targetSection.style.transform = 'translateY(0)';
            targetSection.style.transition = 'all 0.3s ease';
        }, 50);
    }
    
    // Load data for history section if selected
    if (sectionName === 'history') {
        loadHistoryData();
    }
}

// Enhanced refresh function with better feedback
function refreshData() {
    const refreshBtn = document.querySelector('[onclick="refreshData()"]');
    const originalText = refreshBtn.innerHTML;
    
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    refreshBtn.disabled = true;
    
    Promise.all([
        refreshRecentResults(),
        refreshStatistics()
    ]).then(() => {
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
        
        // Show success feedback
        showSuccessMessage('Data refreshed successfully!');
    }).catch(error => {
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
        console.error('Refresh error:', error);
    });
}

// Show success message
function showSuccessMessage(message) {
    const tempSuccess = document.createElement('div');
    tempSuccess.innerHTML = `<i class="fas fa-check"></i> ${message}`;
    tempSuccess.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 1001;
        background: var(--success-color); color: white;
        padding: 1rem 1.5rem; border-radius: var(--border-radius);
        box-shadow: var(--shadow-lg); opacity: 0;
        transition: opacity 0.3s ease; pointer-events: none;
        font-weight: 500;
    `;
    
    document.body.appendChild(tempSuccess);
    setTimeout(() => tempSuccess.style.opacity = '1', 10);
    setTimeout(() => {
        tempSuccess.style.opacity = '0';
        setTimeout(() => document.body.removeChild(tempSuccess), 300);
    }, 3000);
}

// Refresh recent results only
function refreshRecentResults() {
    fetch('api/get-recent.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateRecentResults(data.results);
            }
        })
        .catch(error => {
            console.error('Error refreshing recent results:', error);
        });
}

// Refresh statistics
function refreshStatistics() {
    fetch('api/get-stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatistics(data.stats);
            }
        })
        .catch(error => {
            console.error('Error refreshing statistics:', error);
        });
}

// Update recent results table
function updateRecentResults(results) {
    const container = document.getElementById('recent-results');
    
    if (results.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No script results yet</h3>
                <p>Scripts will appear here once they start reporting their status.</p>
            </div>
        `;
        return;
    }
    
    let html = `
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
    `;
    
    results.forEach(result => {
        const statusIcons = {
            'success': 'fas fa-check',
            'failure': 'fas fa-times', 
            'warning': 'fas fa-exclamation',
            'info': 'fas fa-info'
        };
        
        html += `
            <tr>
                <td>${escapeHtml(result.script_name)}</td>
                <td>${escapeHtml(result.script_type)}</td>
                <td>
                    <span class="status-badge ${result.status}">
                        <i class="${statusIcons[result.status] || 'fas fa-question'}"></i>
                        ${escapeHtml(result.status)}
                    </span>
                </td>
                <td>
                    ${escapeHtml(result.message)}
                    ${result.detailed_message ? `<br><button class="btn btn-sm btn-secondary" data-detailed-message="${escapeHtml(result.detailed_message)}" onclick="showDetailedMessageFromButton(this)"><i class="fas fa-eye"></i> View Details</button>` : ''}
                </td>
                <td>${formatExecutionTime(result.execution_time)}</td>
                <td>${formatDate(result.reported_at)}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
    
    // Re-initialize sorting for the new table
    initializeSortableTables();
}

// Update statistics cards
function updateStatistics(stats) {
    const cards = document.querySelectorAll('.stat-card .stat-value');
    if (cards.length >= 4) {
        cards[0].textContent = stats.scripts_run_today || stats.total_scripts; // fallback for compatibility
        cards[1].textContent = stats.total_executions;
        cards[2].textContent = stats.success_rate + '%';
        cards[3].textContent = stats.failed_executions;
    }
}

// Apply filters for history
function applyFilters() {
    currentPage = 1;
    currentFilters = {
        dateFrom: document.getElementById('date-from').value,
        dateTo: document.getElementById('date-to').value,
        scriptId: document.getElementById('script-filter').value,
        status: document.getElementById('status-filter').value
    };
    
    loadHistoryData();
}

// Load history data with filters and pagination
function loadHistoryData(page = 1) {
    currentPage = page;
    
    const params = new URLSearchParams({
        page: page,
        ...currentFilters
    });
    
    // Show loading spinner
    document.getElementById('history-results').innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
        </div>
    `;
    
    fetch(`api/get-history.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateHistoryResults(data.results);
                updatePagination(data.pagination);
            } else {
                showError('Failed to load history data: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error loading history:', error);
            showError('Failed to load history data');
        });
}

// Update history results table
function updateHistoryResults(results) {
    const container = document.getElementById('history-results');
    
    if (results.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <h3>No results found</h3>
                <p>Try adjusting your filters to see more results.</p>
            </div>
        `;
        return;
    }
    
    let html = `
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
    `;
    
    results.forEach(result => {
        html += `
            <tr>
                <td>${escapeHtml(result.script_name)}</td>
                <td>${escapeHtml(result.script_type)}</td>
                <td>
                    <span class="status-badge ${result.status}">
                        ${escapeHtml(result.status)}
                    </span>
                </td>
                <td>
                    ${escapeHtml(result.message)}
                    ${result.detailed_message ? `<br><button class="btn btn-sm btn-secondary" data-detailed-message="${escapeHtml(result.detailed_message)}" onclick="showDetailedMessageFromButton(this)">View Details</button>` : ''}
                </td>
                <td>${formatExecutionTime(result.execution_time)}</td>
                <td>${formatDate(result.reported_at)}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Update pagination controls
function updatePagination(pagination) {
    const container = document.getElementById('pagination');
    
    if (pagination.total_pages <= 1) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'flex';
    
    let html = '';
    
    // Previous button
    html += `
        <button ${pagination.current_page <= 1 ? 'disabled' : ''} 
                onclick="loadHistoryData(${pagination.current_page - 1})">
            Previous
        </button>
    `;
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    if (startPage > 1) {
        html += `<button onclick="loadHistoryData(1)">1</button>`;
        if (startPage > 2) {
            html += `<span>...</span>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <button class="${i === pagination.current_page ? 'current-page' : ''}" 
                    onclick="loadHistoryData(${i})">
                ${i}
            </button>
        `;
    }
    
    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) {
            html += `<span>...</span>`;
        }
        html += `<button onclick="loadHistoryData(${pagination.total_pages})">${pagination.total_pages}</button>`;
    }
    
    // Next button
    html += `
        <button ${pagination.current_page >= pagination.total_pages ? 'disabled' : ''} 
                onclick="loadHistoryData(${pagination.current_page + 1})">
            Next
        </button>
    `;
    
    container.innerHTML = html;
}

// Export results to CSV
function exportResults() {
    const params = new URLSearchParams({
        export: 'csv',
        ...currentFilters
    });
    
    window.open(`api/export.php?${params}`, '_blank');
}

// Utility functions
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString();
}

function formatExecutionTime(time) {
    if (time === null || time === undefined) return 'N/A';
    return parseFloat(time).toFixed(2) + 's';
}

function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}

function showError(message) {
    // You can implement a toast notification system here
    console.error(message);
    alert(message); // Simple fallback
}

// Auto-refresh functionality
function startAutoRefresh() {
    setInterval(() => {
        if (document.querySelector('#overview.active')) {
            refreshRecentResults();
        }
    }, 30000); // Refresh every 30 seconds
}

// Modal functions
function showDetailedMessage(message) {
    const modal = document.getElementById('detailModal');
    const detailText = document.getElementById('detailText');
    detailText.textContent = message;
    modal.style.display = 'flex';
}

function showDetailedMessageFromButton(button) {
    const message = button.getAttribute('data-detailed-message');
    showDetailedMessage(message);
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.style.display = 'none';
}

// Enhanced modal functions with better animations
function showApiInstructions() {
    const modal = document.getElementById('apiModal');
    modal.style.display = 'flex';
    modal.style.opacity = '0';
    
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.style.transition = 'opacity 0.3s ease';
    }, 10);
    
    // Load API instructions with better loading state
    const instructionsDiv = document.getElementById('apiInstructions');
    instructionsDiv.innerHTML = `
        <div class="loading" style="text-align: center; padding: 2rem;">
            <div class="spinner"></div>
            <p style="margin-top: 1rem; color: var(--medium-gray);">
                <i class="fas fa-code"></i> Loading API documentation...
            </p>
        </div>
    `;
    
    // Fetch README content with cache-busting
    const cacheBuster = new Date().getTime();
    fetch(`api/get-readme.php?t=${cacheBuster}`, {
        cache: 'no-cache',
        headers: {
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('DEBUG INFO:');
                console.log('File path:', data.file_path);
                console.log('File size:', data.file_size, 'bytes');
                console.log('README last modified:', new Date(data.last_modified * 1000));
                console.log('Current time:', new Date(data.timestamp * 1000));
                console.log('Content preview:', data.preview);
                // Convert markdown to HTML (simple conversion)
                const htmlContent = convertMarkdownToHtml(data.content);
                instructionsDiv.innerHTML = htmlContent;
            } else {
                console.error('Error loading README:', data.error);
                console.error('Attempted path:', data.attempted_path);
                instructionsDiv.innerHTML = '<div class="error">Error loading API instructions: ' + data.error + '</div>';
            }
        })
        .catch(error => {
            console.error('Error loading API instructions:', error);
            instructionsDiv.innerHTML = '<div class="error">Error loading API instructions. Please try again.</div>';
        });
}

function closeApiModal() {
    const modal = document.getElementById('apiModal');
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function showDetailedMessageFromButton(button) {
    const message = button.getAttribute('data-detailed-message');
    const modal = document.getElementById('detailModal');
    const detailText = document.getElementById('detailText');
    
    detailText.textContent = message;
    modal.style.display = 'flex';
    modal.style.opacity = '0';
    
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.style.transition = 'opacity 0.3s ease';
    }, 10);
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

// Simple markdown to HTML converter
function convertMarkdownToHtml(markdown) {
    let html = markdown;
    
    // Convert tables first (before other conversions that might interfere)
    html = convertMarkdownTables(html);
    
    // Convert other markdown elements
    html = html
        // Headers (make sure to not affect table content)
        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
        .replace(/^#### (.*$)/gim, '<h4>$1</h4>')
        // Bold
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // Italic
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        // Code blocks (handle multiline)
        .replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>')
        // Inline code
        .replace(/`(.*?)`/g, '<code>$1</code>')
        // Links
        .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>')
        // Convert remaining newlines to breaks (but preserve table structure)
        .replace(/\n(?!<\/?(?:table|thead|tbody|tr|th|td))/g, '<br>');
    
    return '<div class="markdown-content">' + html + '</div>';
}

// Convert markdown tables to HTML
function convertMarkdownTables(markdown) {
    // Use a more precise regex to find table blocks
    const tableRegex = /((?:^\|?[^\n]*\|[^\n]*$\n?)+)/gm;
    
    return markdown.replace(tableRegex, (match) => {
        const lines = match.trim().split('\n');
        
        // Check if this is actually a table (has separator line)
        const hasSeparator = lines.some(line => /^\s*\|?\s*[-:]+\s*\|/.test(line));
        
        if (!hasSeparator || lines.length < 2) {
            return match; // Not a table, return as-is
        }
        
        return convertSingleTable(lines);
    });
}

// Convert a single markdown table to HTML
function convertSingleTable(lines) {
    let html = '<table>';
    let headerProcessed = false;
    let hasValidRows = false;
    
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        
        // Skip empty lines
        if (!line) continue;
        
        // Skip separator lines (like |---|---|---| or |:---|:---:|---:|)
        if (/^\s*\|?\s*[-:]+\s*\|/.test(line)) {
            continue;
        }
        
        // Only process lines that contain pipes
        if (!line.includes('|')) {
            continue;
        }
        
        // Split by | and clean up cells
        let cells = line.split('|');
        
        // Remove first and last empty cells if line starts/ends with |
        if (cells[0].trim() === '') {
            cells = cells.slice(1);
        }
        if (cells[cells.length - 1].trim() === '') {
            cells = cells.slice(0, -1);
        }
        
        // Trim all cells and filter out completely empty ones
        cells = cells.map(cell => cell.trim()).filter((cell, index, arr) => {
            // Keep the cell if it's not empty, or if it's empty but not all cells are empty
            return cell !== '' || arr.some(c => c !== '');
        });
        
        if (cells.length === 0) continue;
        
        if (!headerProcessed) {
            // First valid row is header
            html += '<thead><tr>';
            cells.forEach(cell => {
                html += `<th>${cell || '&nbsp;'}</th>`;
            });
            html += '</tr></thead><tbody>';
            headerProcessed = true;
            hasValidRows = true;
        } else {
            // Data rows
            html += '<tr>';
            cells.forEach(cell => {
                html += `<td>${cell || '&nbsp;'}</td>`;
            });
            html += '</tr>';
            hasValidRows = true;
        }
    }
    
    html += '</tbody></table>';
    
    // Only return the table if we actually processed valid rows
    return hasValidRows ? html : lines.join('\n');
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    });
});

// Auto-refresh functionality with enhanced features
function startAutoRefresh() {
    setInterval(() => {
        if (document.querySelector('#overview.active')) {
            refreshRecentResults().catch(console.error);
        }
    }, 30000); // Refresh every 30 seconds
}

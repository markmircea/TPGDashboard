// Dashboard JavaScript functionality
let currentPage = 1;
let currentFilters = {};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    document.getElementById('date-to').value = formatDateForInput(today);
    document.getElementById('date-from').value = formatDateForInput(thirtyDaysAgo);
    
    // Load initial history data
    loadHistoryData();
    
    // Auto-refresh recent results every 30 seconds
    setInterval(refreshRecentResults, 30000);
});

// Show/hide sections based on tab selection
function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.classList.remove('active'));
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.nav-tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Show selected section
    document.getElementById(sectionName).classList.add('active');
    
    // Add active class to clicked tab
    event.target.classList.add('active');
    
    // Load data for history section if selected
    if (sectionName === 'history') {
        loadHistoryData();
    }
}

// Refresh recent results data
function refreshData() {
    refreshRecentResults();
    refreshStatistics();
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
                <h3>No script results yet</h3>
                <p>Scripts will appear here once they start reporting their status.</p>
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

// Update statistics cards
function updateStatistics(stats) {
    const cards = document.querySelectorAll('.stat-card .stat-value');
    if (cards.length >= 4) {
        cards[0].textContent = stats.total_scripts;
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

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('detailModal');
    if (event.target === modal) {
        closeDetailModal();
    }
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', startAutoRefresh);

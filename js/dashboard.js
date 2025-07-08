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

// API Instructions Modal functions
function showApiInstructions() {
    const modal = document.getElementById('apiModal');
    const instructionsContainer = document.getElementById('apiInstructions');
    
    // Show loading
    instructionsContainer.innerHTML = '<div class="loading"><div class="spinner"></div><p>Loading API instructions...</p></div>';
    modal.style.display = 'flex';
    
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
                instructionsContainer.innerHTML = htmlContent;
            } else {
                console.error('Error loading README:', data.error);
                console.error('Attempted path:', data.attempted_path);
                instructionsContainer.innerHTML = '<div class="error">Error loading API instructions: ' + data.error + '</div>';
            }
        })
        .catch(error => {
            console.error('Error loading API instructions:', error);
            instructionsContainer.innerHTML = '<div class="error">Error loading API instructions. Please try again.</div>';
        });
}

function closeApiModal() {
    const modal = document.getElementById('apiModal');
    modal.style.display = 'none';
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

// Close modal when clicking outside of it
window.onclick = function(event) {
    const detailModal = document.getElementById('detailModal');
    const apiModal = document.getElementById('apiModal');
    
    if (event.target === detailModal) {
        closeDetailModal();
    }
    
    if (event.target === apiModal) {
        closeApiModal();
    }
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', startAutoRefresh);

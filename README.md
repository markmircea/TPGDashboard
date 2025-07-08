# TPG Dashboard - Script Monitoring System

A web-based dashboard for monitoring the status and results of TPG scripts. Built with PHP, HTML, CSS, and vanilla JavaScript with MySQL

## Usage

### For Dashboard Users

1. **Login** to the dashboard using the provided credentials
2. **Overview Tab**: View recent script activity and real-time statistics
3. **Scripts Tab**: See all registered scripts and their performance metrics
4. **History Tab**: Filter and analyze historical script results
   - Filter by date range, script name, or status
   - Export filtered results to CSV
5. **Auto-refresh**: The overview section automatically refreshes every 30 seconds

### For Script Developers

#### API Endpoint

Scripts can report their status by sending a POST request to `/api/report.php` with JSON data:

```json
{
    "script_name": "your_script_name",
    "script_type": "script_category",
    "status": "success|failure|warning|info",
    "message": "Brief status message",
    "detailed_message": "Comprehensive execution details with logs, errors, etc.",
    "execution_time": 45.2,
    "description": "Optional script description"
}
```

#### Required Fields
- `script_name`: Unique identifier for your script
- `status`: One of "success", "failure", "warning", or "info"

#### Optional Fields
- `script_type`: Category/type of script (default: "general")
- `message`: Brief status or error message (displayed in main table)
- `detailed_message`: Comprehensive details, logs, error traces, etc. (viewable via "View Details" button)
- `execution_time`: Script execution time in seconds
- `description`: Script description (used on first registration)

#### Status Types
- **success**: Script completed successfully
- **failure**: Script failed with errors
- **warning**: Script completed but with warnings or issues
- **info**: Informational status (e.g., scheduled maintenance, notifications)

#### Example Usage

**PHP Example:**
```php
$data = [
    'script_name' => 'sftp_download_script',
    'script_type' => 'file_transfer',
    'status' => 'success',
    'message' => 'Downloaded 15 files successfully',
    'execution_time' => 45.2
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents('http://your-domain.com/api/report.php', false, $context);
```

**cURL Example:**
```bash
curl -X POST http://your-domain.com/api/report.php \
  -H "Content-Type: application/json" \
  -d '{
    "script_name": "backup_script",
    "script_type": "maintenance",
    "status": "success",
    "message": "Backup completed successfully",
    "execution_time": 120.5
  }'
```

**Powershell Example:**
```powershell
Invoke-WebRequest -Uri "http://localhost:8000/api/report.php" -Method POST -Headers @{"Content-Type"="application/json"} -Body '{"script_name":"detailed_test","status":"info","message":"Test with detailed info","detailed_message":"This is a very detailed message that contains:\n\n1. Multiple lines of information\n2. Technical details about the process\n3. Error logs or debug information\n4. Any other relevant data that might be too long for the main message field\n\nThis demonstrates how detailed messages can provide comprehensive information about script execution."}'
```

#### Complete Request Body Reference

All supported fields in the API request:

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `script_name` | String | ✅ Yes | Unique identifier for your script | `"sftp_download_script"` |
| `status` | String (ENUM) | ✅ Yes | Execution status: `success`, `failure`, `warning`, `info` | `"success"` |
| `script_type` | String | ❌ No | Category/type of script (default: "general") | `"file_transfer"` |
| `message` | String (TEXT) | ❌ No | Brief status message shown in main table | `"Downloaded 15 files"` |
| `detailed_message` | String (LONGTEXT) | ❌ No | Comprehensive details, logs, errors (viewable via modal) | `"Server: sftp.example.com\nFiles: file1.csv, file2.csv"` |
| `execution_time` | Decimal | ❌ No | Script execution time in seconds | `45.2` |
| `description` | String (TEXT) | ❌ No | Script description (used on first registration) | `"Downloads files from SFTP server"` |

**Complete Example with All Fields:**
```json
{
    "script_name": "comprehensive_backup_script",
    "script_type": "backup",
    "status": "warning",
    "message": "Backup completed with 2 warnings",
    "detailed_message": "Backup Process Report:\n\nStart Time: 2024-01-15 02:00:00\nEnd Time: 2024-01-15 02:45:30\n\nFiles Processed:\n✓ database_backup.sql (2.3 GB)\n✓ uploads_backup.tar.gz (1.8 GB)\n⚠ logs_backup.tar.gz (corrupted, retried successfully)\n⚠ config_backup.zip (permission warning)\n\nStorage Locations:\n- Primary: /backups/daily/2024-01-15/\n- Mirror: s3://company-backups/daily/2024-01-15/\n\nVerification:\n✓ Checksums verified\n✓ Restore test passed\n\nWarnings:\n1. Log file had permission issues (resolved)\n2. Config backup size larger than expected\n\nNext scheduled backup: 2024-01-16 02:00:00",
    "execution_time": 2730.5,
    "description": "Comprehensive nightly backup system for databases, files, and configurations"
}
```

### Testing

Run the included test script to generate sample data:

```bash
php test-script.php
```

This will simulate an SFTP download script and report results to the dashboard.

## Database Schema

The application uses MySQL with three main tables:

- **users**: User authentication
- **scripts**: Registered scripts and their metadata
- **script_results**: Individual script execution results

## Security Features

- Session-based authentication with timeout
- SQL injection prevention using prepared statements
- XSS protection with output escaping
- Input validation and sanitization
- CSRF protection for forms

## Customization

### Changing Default Credentials

Edit `includes/config.php`:
```php
define('ADMIN_USERNAME', 'your_username');
define('ADMIN_PASSWORD', 'your_password');
```

### Styling

- Modify `css/style.css` for general styling and login page
- Modify `css/dashboard.css` for dashboard-specific styling
- The design is fully responsive and uses CSS Grid/Flexbox

### Adding Features

- Add new API endpoints in the `api/` directory
- Extend database functions in `includes/functions.php`
- Add JavaScript functionality in `js/dashboard.js`

## Troubleshooting

### Common Issues

1. **Database Permission Errors**
   - Ensure the `database/` directory is writable by the web server
   - Check file permissions: `chmod 755 database/`

2. **API Not Working**
   - Verify the API URL in your scripts matches your server configuration
   - Check web server error logs for PHP errors

3. **Login Issues**
   - Verify credentials in `includes/config.php`
   - Check if sessions are working (session.save_path writable)

4. **JavaScript Errors**
   - Check browser console for errors
   - Ensure all API endpoints are accessible

### Debug Mode

To enable debug mode, add this to `includes/config.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## License

This project is open source. Feel free to modify and distribute according to your needs.

## Support

For issues and questions, check the troubleshooting section above or review the code comments for implementation details.


## Features

- **Authentication System**: Simple username/password login with session management
- **Real-time Monitoring**: View recent script executions and their status
- **Script Management**: Automatic registration of new scripts on first report
- **Historical Analysis**: Filter and search through historical script results by date, script type, and status
- **Statistics Dashboard**: Overview of success rates, total executions, and script performance
- **API Endpoint**: RESTful API for scripts to report their status
- **Export Functionality**: Export filtered results to CSV format
- **Responsive Design**: Works on desktop and mobile devices

## Project Structure

```
TPGDashboard/
├── index.php              # Login page
├── dashboard.php           # Main dashboard interface
├── test-script.php         # Sample script for testing
├── includes/
│   ├── config.php         # Database and app configuration
│   ├── auth.php           # Authentication functions
│   └── functions.php      # Database operations and utilities
├── api/
│   ├── report.php         # Main API endpoint for script reporting
│   ├── get-recent.php     # Get recent script results
│   ├── get-stats.php      # Get dashboard statistics
│   ├── get-history.php    # Get filtered historical results
│   └── export.php         # Export results to CSV
├── css/
│   ├── style.css          # Main styles and login page
│   └── dashboard.css      # Dashboard-specific styles
├── js/
│   └── dashboard.js       # Dashboard JavaScript functionality
└── database/
    └── dashboard.db       # MySQL database (auto-created)
```

## Installation

### Requirements

- PHP 7.4 or higher
- MySQL (usually included)
- Web server (Apache, Nginx, or PHP built-in server)

### Setup Steps

1. **Clone or download** the project to your web server directory:
   ```bash
   git clone <repository-url> /path/to/webroot/TPGDashboard
   ```

2. **Set permissions** for the database directory:
   ```bash
   chmod 755 database/
   chmod 666 database/dashboard.db  # If database file exists
   ```

3. **Configure the application** (optional):
   - Edit `includes/config.php` to change default credentials or database path
   - Default login: username `admin`, password `admin123`

4. **Start your web server**:
   
   **Option A: PHP Built-in Server (for development)**
   ```bash
   cd /path/to/TPGDashboard
   php -S localhost:8000
   ```
   
   **Option B: Apache/Nginx**
   - Ensure the project is in your web root directory
   - Access via your configured domain/IP

5. **Access the dashboard**:
   - Open your browser and navigate to `http://localhost:8000` (or your configured URL)
   - Login with username: `admin`, password: `admin123`


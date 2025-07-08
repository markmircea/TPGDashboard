# TPG Dashboard - Script Monitoring System

A web-based dashboard for monitoring the status and results of company scripts. Built with PHP, HTML, CSS, and vanilla JavaScript with SQLite database.

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
    └── dashboard.db       # SQLite database (auto-created)
```

## Installation

### Requirements

- PHP 7.4 or higher
- SQLite extension for PHP (usually included)
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
    "status": "success|failure",
    "message": "Detailed status message",
    "execution_time": 45.2,
    "description": "Optional script description"
}
```

#### Required Fields
- `script_name`: Unique identifier for your script
- `status`: Either "success" or "failure"

#### Optional Fields
- `script_type`: Category/type of script (default: "general")
- `message`: Detailed status or error message
- `execution_time`: Script execution time in seconds
- `description`: Script description (used on first registration)

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

**Python Example:**
```python
import requests
import json

data = {
    'script_name': 'data_sync_script',
    'script_type': 'data_processing',
    'status': 'failure',
    'message': 'Database connection failed',
    'execution_time': 5.3
}

response = requests.post(
    'http://your-domain.com/api/report.php',
    headers={'Content-Type': 'application/json'},
    data=json.dumps(data)
)
```

### Testing

Run the included test script to generate sample data:

```bash
php test-script.php
```

This will simulate an SFTP download script and report results to the dashboard.

## Database Schema

The application uses SQLite with three main tables:

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

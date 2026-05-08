# Dayem ERP

A comprehensive Enterprise Resource Planning system designed for modern businesses, built with clean PHP architecture and intuitive Arabic-first interface.

## Overview

Dayem ERP helps organizations manage their daily operations efficiently. From employee management to task tracking, salary processing to internal support tickets — everything in one place, accessible from anywhere.

## Features

### Core Modules

| Module | Description |
|--------|-------------|
| **Authentication** | Secure login with email/phone, password protection, session management, and role-based access (Admin/Employee) |
| **Departments** | Create and manage company departments with manager assignment and default salary configurations |
| **Employees** | Complete employee lifecycle management — add, edit, track status, generate employee codes, and upload documents |
| **Tasks** | Assign tasks to multiple users, track progress, add notes and attachments, automatic overdue detection |
| **Tickets** | Internal support system with chat-style messaging, file attachments, and status workflow |
| **Salaries** | Monthly salary tracking, payment confirmation, history view, and reporting |
| **Files** | Personal document management with metadata, priority levels, and search functionality |
| **Logs** | Complete audit trail of all system activities with CSV export capability |
| **Dashboard** | At-a-glance KPIs and trend charts for system overview |

### Key Highlights

- **Arabic RTL Interface** — Native right-to-left design for Arabic users
- **Soft Delete** — Data integrity with recoverable deletions
- **CSRF Protection** — Security-first approach on all forms
- **Responsive Design** — Works on desktop and tablet devices

## Tech Stack

- **Backend**: Native PHP 8.x (Custom MVC Framework)
- **Database**: MySQL
- **Frontend**: Tailwind CSS, Vanilla JavaScript
- **Architecture**: Feature-first modular design

## Requirements

- PHP 8.x or higher
- MySQL 5.7+ / MariaDB 10.3+
- Apache/Nginx web server
- XAMPP/WAMP/LAMP stack (for local development)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/medohack9/dayem_Erp.git
cd dayem_Erp
```

### 2. Configure Database

Copy the example configuration and update with your credentials:

```bash
cp config/database.example.php config/database.php
```

Edit `config/database.php`:

```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'dayem_erp',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
```

### 3. Create Database

Create a new MySQL database:

```sql
CREATE DATABASE dayem_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Run Migrations

Execute the SQL migrations in order:

```bash
mysql -u your_username -p dayem_erp < database/migrations/001_core_foundation.sql
mysql -u your_username -p dayem_erp < database/migrations/002_authentication.sql
mysql -u your_username -p dayem_erp < database/migrations/003_departments.sql
mysql -u your_username -p dayem_erp < database/migrations/004_employees.sql
mysql -u your_username -p dayem_erp < database/migrations/005_employee_account_documents.sql
mysql -u your_username -p dayem_erp < database/migrations/006_tasks.sql
mysql -u your_username -p dayem_erp < database/migrations/007_task_notes_media.sql
mysql -u your_username -p dayem_erp < database/migrations/008_tickets.sql
mysql -u your_username -p dayem_erp < database/migrations/009_salaries.sql
mysql -u your_username -p dayem_erp < database/migrations/010_files.sql
```

### 5. Configure Web Server

Point your web server's document root to the `public` directory.

**For Apache** — The included `.htaccess` handles URL rewriting automatically.

**For Nginx** — Add this to your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 6. Set Permissions

Ensure write permissions for storage directories:

```bash
chmod -R 755 storage/logs
chmod -R 755 storage/uploads
```

### 7. Access the Application

Open your browser and navigate to your configured URL. 

**Default Admin Account** (created by migrations):
- Email: `admin@dayem.com`
- Password: `admin123`

> **Important**: Change the default admin password immediately after first login.

## Project Structure

```
├── app/
│   ├── controllers/     # Request handlers
│   ├── models/           # Database interactions
│   ├── views/            # UI templates
│   ├── core/             # Framework core (Router, Database, etc.)
│   ├── middleware/       # Request filters
│   └── helpers.php       # Utility functions
├── config/
│   ├── app.php           # Application settings
│   ├── database.php      # Database credentials (gitignored)
│   └── routes.php        # URL routing definitions
├── database/
│   └── migrations/       # SQL schema files
├── public/
│   ├── index.php         # Application entry point
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
├── storage/
│   ├── logs/             # Application logs
│   └── uploads/          # User uploaded files
└── specs/                # Feature specifications
```

## User Roles

| Role | Permissions |
|------|-------------|
| **Admin** | Full access to all modules, employee management, salary processing, system logs |
| **Employee** | View assigned tasks, manage own files, create tickets, update profile |

## Security Features

- Password hashing with bcrypt
- CSRF token validation on all forms
- Session timeout management
- Login attempt limiting with lockout
- Input sanitization and validation
- SQL injection prevention via PDO prepared statements

## Contributing

This project follows a feature-first development approach. Each feature includes database, backend, and frontend layers completed together.

## Roadmap

### Planned for Future Releases

- Attendance tracking system (QR/manual)
- Rewards and penalties module
- Multi-branch support
- Mobile application
- Real-time notifications

## License

This project is proprietary software. All rights reserved.

## Support

For questions or issues, please open a ticket in the repository or contact the development team.

---

Built with care for modern businesses.
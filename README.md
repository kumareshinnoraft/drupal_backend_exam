# Drupal Backend Exam

A Drupal 10 backend examination project demonstrating content management capabilities with custom user roles and permissions.

## Project Overview

This project is built using Drupal 10 with the recommended project template, featuring a standard content management setup with custom content editor roles. It includes pre-configured content types, taxonomy, and user permissions suitable for a backend development assessment.

## Prerequisites

- PHP 8.1 or higher
- Composer 2.x
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache or Nginx)
- Drush (included via Composer)

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/kumareshinnoraft/drupal_backend_exam.git
cd drupal_backend_exam
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
Create a MySQL database for the project:
```sql
CREATE DATABASE drupal_backend_exam;
CREATE USER 'drupal_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON drupal_backend_exam.* TO 'drupal_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Site Installation
```bash
# Navigate to the web directory
cd web

# Install Drupal using Drush
../vendor/bin/drush site:install --db-url=mysql://drupal_user:your_password@localhost/drupal_backend_exam --site-name="Drupal Backend Exam" --account-name=admin --account-pass=admin123
```

### 5. Import Configuration
```bash
# Import the exported configuration
../vendor/bin/drush config:import
```

## Configuration

### Custom User Roles

The project includes a custom **Content Editor** role with the following permissions:
- Access administration pages and toolbar
- Create and edit Article and Page content
- Manage URL aliases
- Create and edit taxonomy terms in Tags vocabulary
- Manage content revisions
- Access contextual links and tours

### Content Types

- **Article**: Standard Drupal article content type with image field and tags
- **Page**: Basic page content type for static content

### Taxonomy

- **Tags**: Vocabulary for tagging content with unlimited terms

## Development Workflow

### Configuration Management
```bash
# Export configuration changes
./vendor/bin/drush config:export

# Import configuration
./vendor/bin/drush config:import

# Check configuration status
./vendor/bin/drush config:status
```

### Cache Management
```bash
# Clear all caches
./vendor/bin/drush cache:rebuild

# Clear specific caches
./vendor/bin/drush cache:clear css-js
```

### Database Updates
```bash
# Run database updates
./vendor/bin/drush updatedb

# Check for pending updates
./vendor/bin/drush updatedb:status
```

## Project Structure

```
drupal_backend_exam/
├── composer.json          # Dependency management
├── composer.lock          # Locked dependency versions
├── config/
│   └── sync/              # Exported Drupal configuration
├── web/                   # Document root
│   ├── core/              # Drupal core files
│   ├── modules/           # Contributed and custom modules
│   ├── themes/            # Contributed and custom themes
│   ├── sites/             # Site-specific files and settings
│   └── index.php          # Main entry point
└── vendor/                # Composer dependencies
```

## User Accounts

After installation, you can create test accounts with different roles:

### Administrator
- Username: admin
- Password: admin123
- Role: Administrator (full access)

### Content Editor Test Account
```bash
# Create content editor user via Drush
./vendor/bin/drush user:create content_editor --password="editor123"
./vendor/bin/drush user:role:add content_editor content_editor
```

## Testing the Setup

1. Access the site at your configured URL
2. Log in with administrator credentials
3. Navigate to `/admin/people` to verify user roles
4. Create test content as different user roles
5. Verify permissions are working correctly

## Troubleshooting

### Common Issues

**Permissions Error**: Ensure web server has write permissions to `web/sites/default/files`
```bash
chmod -R 775 web/sites/default/files
```

**Database Connection Error**: Verify database credentials in `web/sites/default/settings.php`

**Configuration Import Error**: Clear cache and retry:
```bash
./vendor/bin/drush cache:rebuild
./vendor/bin/drush config:import
```

## License

This project is licensed under GPL-2.0-or-later, consistent with Drupal core licensing.

## Support

For questions about this examination project, please refer to:
- [Drupal Documentation](https://www.drupal.org/docs)
- [Drupal Community Support](https://www.drupal.org/support)
- [Drush Documentation](https://www.drush.org/)

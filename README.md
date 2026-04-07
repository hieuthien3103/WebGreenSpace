# WebGreenSpace 🌿

E-commerce website cho cây cảnh và đồ trang trí xanh.

## Features

- 🛍️ Product catalog with categories
- 🔍 Search and filter functionality
- 🛒 Shopping cart
- 📱 Responsive design
- 🌙 Dark mode support
- 🎨 Modern UI with Tailwind CSS

## Tech Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML, Tailwind CSS, JavaScript
- **Architecture**: MVC Pattern with Service Layer

## Recent Refactoring (v2.0)

✅ Type-safe code with PHP 8+ type hints
✅ Service Layer architecture
✅ Clean separation of concerns
✅ Comprehensive documentation
✅ Router system for clean URLs

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB
- Apache/Nginx web server (or PHP built-in server for development)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd WebGreenSpace
```

2. Configure database:
- Import database schema from `database/schema_revised.sql`
- Import sample data from `database/sample_data_revised.sql`
- Update `config/config.php` with your database credentials

3. Start development server:
```bash
cd public
php -S localhost:8000
```

Or run the full stack with Docker:
```bash
docker compose up -d --build
```

4. Access the website:
```
http://localhost:8000
```

phpMyAdmin (Docker):
```
http://localhost:8080
```

## Project Structure

```
WebGreenSpace/
├── app/
│   ├── controllers/     # Request handlers
│   ├── models/         # Database models
│   ├── services/       # Business logic layer
│   ├── core/           # Core components (Router)
│   └── views/          # View templates
├── config/             # Configuration files
├── database/           # Database schemas and migrations
├── docs/               # Documentation files
├── helpers/            # Helper functions
├── public/             # Public web root
│   ├── admin/         # Admin tools
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   ├── images/        # Static images
│   ├── includes/      # Shared templates
│   └── index.php      # Entry point
├── tests/              # Test files
└── uploads/            # User uploaded files
```

## Documentation

- [Refactoring Guide](docs/REFACTORING_GUIDE.md) - Detailed refactoring documentation
- [Summary](docs/REFACTORING_SUMMARY.md) - Quick overview of changes
- [Products Guide](docs/PRODUCTS_GUIDE.md) - Product management guide
- [Image Upload Guide](docs/IMAGE_UPLOAD_GUIDE.md) - Image handling guide
- [Backend Checklist](docs/backend_checklist.md) - Current backend completion checklist
- [Report Index](docs/report_index.md) - Entry point for ERD, sequence, system flow, and Docker docs

## Development

### Code Standards
- PSR-12 coding standard
- Type hints for all functions/methods
- PHPDoc comments for documentation
- Clean code principles

### Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes with proper type hints
4. Test thoroughly
5. Submit a pull request

## License

[Your License Here]

## Authors

GreenSpace Team

## Changelog

### v2.0 - Refactored (January 2026)
- Added Service Layer architecture
- Implemented type safety with PHP 8+
- Created Router system
- Improved documentation
- Enhanced code quality

### v1.0 - Initial Release
- Basic e-commerce functionality
- Product catalog
- Shopping cart
- Responsive design

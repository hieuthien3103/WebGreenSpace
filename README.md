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
- Import database schema from `database/schema.sql`
- Import sample data from `database/sample_data.sql`
- Update `config/config.php` with your database credentials

3. Start development server:
```bash
cd public
php -S localhost:8000
```

4. Access the website:
```
http://localhost:8000
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
├── helpers/            # Helper functions
├── public/             # Public web root
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   ├── images/        # Static images
│   └── index.php      # Entry point
└── uploads/            # User uploaded files
```

## Documentation

- [Refactoring Guide](REFACTORING_GUIDE.md) - Detailed refactoring documentation
- [Summary](REFACTORING_SUMMARY.md) - Quick overview of changes

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

# Admin Tools

This directory contains administrative tools for managing the website.

## Available Tools

### Image Management
- `admin_upload_images.php` - Bulk image upload interface
- `check_images.php` - Check image availability and status
- `fix_images.php` - Fix broken image paths
- `update_product_image.php` - Update product images
- `upload_product_image.php` - Single product image upload

### Utilities
- `create_placeholder.php` - Generate placeholder images
- `clear_cache.php` - Clear application cache

## Access

These tools are for administrative purposes only. In production, these should be:
1. Protected with authentication
2. Restricted by IP address
3. Moved behind admin panel

## Security Note

⚠️ **Important**: These files should not be publicly accessible in production. Use proper authentication and authorization.

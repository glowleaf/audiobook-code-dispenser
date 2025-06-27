# Audiobook Code Dispenser WordPress Plugin

A WordPress plugin for dispensing audiobook promotional codes from CSV uploads with MailerLite integration.

## Features

- **Admin Interface**: Manage audiobook titles and upload CSV files with promotional codes
- **CSV Import**: Upload codes for US and UK marketplaces separately
- **Automatic Code Dispensing**: Dispense codes automatically when users request them
- **MailerLite Integration**: Automatically add users to your mailing list
- **Shortcode Support**: Display the widget anywhere using `[audiobook_dispenser]`
- **Regional Support**: Separate handling for US and UK Audible codes
- **Code Status Tracking**: Track available, dispensed, and redeemed codes
- **Responsive Design**: Beautiful, modern UI that works on all devices

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Audiobook Dispenser' in the WordPress admin menu
4. Configure your MailerLite settings in the Settings page

## Configuration

### MailerLite Setup

1. Go to **Audiobook Dispenser > Settings**
2. Enter your MailerLite API Key
3. Enter your MailerLite Group ID
4. Customize the widget title and subtitle if desired

### CSV File Format

Your CSV files should have the following columns:
- **Promo Code**: The audiobook promotional code (e.g., "29RQ9B4U4EL7J")
- **Status**: AVAILABLE, REDEEMED, or DISPENSED
- **Marketplace**: US or GB
- **Generated On**: Date the code was generated in MM/DD/YY format (e.g., "04/30/20")
- **Redemption Date**: Date the code was redeemed in MM/DD/YY format (empty if not redeemed)
- **Shared**: Whether the code has been shared ("true" or "false")

Example CSV format:
```csv
"Promo Code","Status","Marketplace","Generated On","Redemption Date","Shared"
"29RQ9B4U4EL7J","REDEEMED","GB","04/30/20","05/28/24","false"
"2G8RWTHWWGPPD","AVAILABLE","GB","04/30/20","","false"
"22ZRJTSTPQ5NK","REDEEMED","US","01/13/20","03/17/20","false"
```

### File Naming Convention

CSV files should be named in the format: `promocodes-BookTitle-YYYY-MM-DD.csv`

Examples:
- `promocodes-7 Deadly Roommates Mean Gods Book 1-2025-06-27.csv`
- `promocodes-A Trillion Dollar Rock-2025-06-27.csv`

The book title will be automatically extracted from the filename and spaces will replace hyphens.

## Usage

### Admin Interface

1. Go to **Audiobook Dispenser** in the WordPress admin menu
2. View statistics and manage books
3. Click "Upload Codes" for any book to upload a CSV file
4. Select the marketplace (US or GB) and upload your CSV file
5. The plugin will automatically import codes and mark books as available/unavailable

### Frontend Display

Use the shortcode to display the widget:

```
[audiobook_dispenser]
```

Or customize the title and subtitle:

```
[audiobook_dispenser title="Get Free Audiobooks" subtitle="Choose from our collection"]
```

### How It Works

1. Users select their region (US or UK)
2. Users choose from available books (books with no codes are greyed out)
3. Users enter their email address
4. The plugin dispenses the next available code
5. Users are automatically added to your MailerLite list
6. The code is displayed and the user receives instructions

## Database Tables

The plugin creates three tables:

- `wp_acd_books`: Stores book information
- `wp_acd_codes`: Stores promotional codes
- `wp_acd_settings`: Stores plugin settings

## Book Management

The plugin comes pre-loaded with all your book titles. You can:

- Upload codes for any book
- Delete books (this removes all associated codes)
- View code statistics for each book
- See which books have available codes for each region

## Code Status Management

- **AVAILABLE**: Codes ready to be dispensed
- **DISPENSED**: Codes that have been given to users
- **REDEEMED**: Codes that were already redeemed (imported from CSV)

## Styling

The plugin includes beautiful, responsive CSS that matches modern design standards. The frontend widget features:

- Gradient backgrounds and buttons
- Smooth animations and transitions
- Mobile-responsive design
- Clear visual feedback for user actions

## Security Features

- Nonce verification for all AJAX requests
- Capability checks for admin functions
- Input sanitization and validation
- SQL injection prevention

## Support

For support or feature requests, please contact the plugin author.

## License

GPL v2 or later 
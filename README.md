# GetJerseys - Football Jersey E-commerce Website

GetJerseys is a comprehensive e-commerce platform specialized in selling football jerseys from national teams and clubs worldwide. This website allows users to browse and purchase jerseys while providing administrators with tools to manage products, orders, and the website.

## Features

### User Features
- Browse jerseys by different categories (national teams, club teams)
- Filter jerseys by type (home, away, third kits)
- View detailed product information
- Shopping cart functionality
- Checkout process without authentication
- Responsive design for all devices

### Admin Features
- Secure authentication system
- Dashboard with key statistics
- Product management (add, edit, delete)
- Category management (national teams, club teams)
- Order management and status updates
- Admin user management

## Technology Stack

- **Frontend**: HTML, CSS, Bootstrap 5
- **Backend**: PHP
- **Database**: MySQL

## Installation Instructions

1. Clone this repository to your local server environment
2. Import the database schema from `setup/database.sql`
3. Update database connection settings in `config/database.php`
4. Make sure your web server has write permissions for the `assets/images/products` directory
5. Access the website through your local server

## Admin Access

Use the following credentials to access the admin dashboard:

- **Username**: admin
- **Password**: admin123

## Directory Structure

```
getjerseys/
├── admin/              # Admin dashboard files
├── assets/             # Static assets (CSS, JS, images)
├── config/             # Configuration files
├── includes/           # Shared PHP components
├── setup/              # Setup files (database schema)
└── index.php           # Main entry point
```

## Category Structure

- **National Teams**
  - Continents (Europe, South America, etc.)
    - Countries (France, Brazil, etc.)

- **Club Teams**
  - Leagues (Premier League, La Liga, etc.)
    - Clubs (Manchester United, Barcelona, etc.)

## License

This project is licensed under the MIT License.
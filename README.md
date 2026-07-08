# BiblioTech - Library Management System

A complete library management system built with PHP and MySQL. Handles book cataloging, member accounts, borrowing requests, and basic reporting.

## Features

### For Librarians (Admin)
- **Book Management**: Add, edit, delete books with cover image uploads
- **Catalog Organization**: Manage authors and categories
- **Google Books Integration**: Import book details directly from Google Books API
- **Borrow Requests**: Review, approve, or reject member borrowing requests
- **Member Management**: Create and manage member accounts
- **Reports**: View statistics, borrowing trends, and activity logs

### For Members
- **Browse Books**: Search and filter through the library catalog
- **Request Books**: Submit borrowing requests for available books
- **Track Borrowings**: View current and past borrowing history
- **Profile Management**: Update personal information
- **Pay Fines**: Process overdue fine payments via Stripe

### System Features
- **Role-Based Access**: Separate dashboards for admins and members
- **Automatic Stock Tracking**: Book quantity updates on checkout/return
- **Overdue Fine Calculation**: Automatic fine calculation for late returns
- **Email Notifications**: Simulated email system for important updates
- **Responsive Design**: Works on desktop and mobile devices

## Tech Stack

- **Backend**: PHP 8.x
- **Database**: MySQL with PDO
- **Frontend**: Bootstrap 5, HTML5, CSS3, JavaScript
- **Charts**: Chart.js for data visualization
- **Payments**: Stripe integration (optional)
- **External API**: Google Books API

## Installation

1. Copy project to XAMPP htdocs
2. Create MySQL database named `library_db`
3. Import `database.sql`
4. Configure settings in [config/config.php](cci:7://file:///c:/xampp/htdocs/Library%20Management%20System/config/config.php:0:0-0:0)
5. Access via browser

## Default Credentials

**Admin**: admin@library.com / admin123
**Member**: member@library.com / member123

## Project Structure

- `config/` - Database and app configuration
- `app/` - Models, controllers, and helpers
- `views/` - PHP templates and layouts
- `public/` - Web root and assets
- `database.sql` - Database schema

## License

This project is open source and available for educational purposes.

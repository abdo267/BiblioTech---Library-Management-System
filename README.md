# BiblioTech - Library Management System

A simple library management system built with PHP and MySQL. Handles book cataloging, member accounts, borrowing requests, and basic reporting.

## Features

**Security:**
- PDO prepared statements for SQL injection protection
- Password hashing with PHP's built-in functions
- Role-based access (admin vs member)
- Basic input validation

**Admin Features:**
- Add/edit/delete books with cover images
- Manage authors and categories
- Import books from Google Books API
- Approve or reject borrowing requests
- View activity logs and basic reports

**Member Features:**
- Browse and search books
- Request books for borrowing
- View borrowing history
- Profile management

**Circulation:**
- Automatic stock tracking when books are checked out/returned
- Overdue fine calculation
- Payment processing (Stripe integration available)

---

## Folder Structure

```
Library Management System/
├── config/           # Database and app configuration
├── app/
│
│   ├── Models/       # Database models (User, Book, Member, etc.)
│   ├── Controllers/  # Request handlers
│   └── Helpers/      # Utility functions
│
├── views/            # PHP templates
│   ├── layouts/      # Header, footer, sidebar
│   ├── auth/         # Login, register pages
│   ├── admin/        # Admin dashboard and management pages
│   └── member/       # Member pages
│
├── public/           # Web root
│   ├── index.php     # Entry point
│   ├── assets/       # CSS and JS files
│   └── uploads/      # Book cover images
│
├── database.sql      # Database schema
├── README.md
└── INSTALLATION.md
```

---

## Getting Started

See [INSTALLATION.md](INSTALLATION.md) for setup instructions.

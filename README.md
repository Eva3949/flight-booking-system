# Flight Booking System

A comprehensive web-based flight booking application built with PHP and MySQL, featuring user authentication, flight search, booking management, payment processing, and a full admin panel for system administration.

## 🌟 Features

### For Passengers
- **User Registration & Authentication** - Secure login and registration system
- **Flight Search** - Search flights by source, destination, and date
- **Seat Selection** - Interactive seat selection during booking
- **Booking Management** - View and manage personal bookings
- **Payment Processing** - Secure payment integration with transaction support
- **Booking Cancellation** - Cancel bookings with refund processing
- **Booking History** - Track all past and upcoming bookings

### For Administrators
- **Dashboard** - Real-time statistics and overview
- **Flight Management** - Add, edit, and manage flights
- **Aircraft Management** - Manage aircraft fleet
- **Booking Management** - View and manage all bookings
- **Passenger Management** - View passenger information
- **Staff Management** - Add and manage staff accounts
- **Revenue Tracking** - Monitor total revenue and payments
- **Batch Operations** - Bulk update flights and data

## 🛠 Tech Stack

- **Backend**: PHP 8.2
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome 6.0
- **Server**: Apache (XAMPP/WAMP)

## 📋 Prerequisites

- XAMPP, WAMP, or any PHP/MySQL server
- PHP 8.0 or higher
- MySQL 5.7 or higher / MariaDB
- Web browser (Chrome, Firefox, Edge, etc.)

## 🚀 Installation

### Step 1: Clone the Repository
```bash
git clone https://github.com/eva3949/flight-booking-system.git
```

### Step 2: Copy to htdocs
- Copy the `flight-booking-system` folder to your XAMPP `htdocs` directory
- Path: `C:\xampp\htdocs\flight-booking-system`

### Step 3: Database Setup

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click on the **Databases** tab
3. Create a new database named: `flight_booking_system`
4. Click on the **Import** tab
5. Select the `database.sql` file from the project folder
6. Click **Go** to import

### Step 4: Configure Database Connection

Open `includes/config.php` and verify database settings:

```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'flight_booking_system');
define('DB_PORT', '3306');
```

### Step 5: Update Site URL

In `includes/config.php`, update the SITE_URL if needed:

```php
define('SITE_URL', 'http://localhost/flight-booking-system');
```

## 🎯 Usage

### Access the Application
- **Main Site**: `http://localhost/flight-booking-system/index.php`
- **Admin Panel**: `http://localhost/flight-booking-system/admin/index.php`

### Default Admin Credentials
- **Email**: evadevstudio@gmail.com
- **Password**: admin123

### Getting Started
1. Register a new user account on the main site
2. Login with your credentials
3. Search for flights by selecting source and destination
4. Select a flight and choose your seats
5. Complete the booking process
6. Make payment to confirm your booking
7. View your bookings in "My Bookings" section

## 📁 Project Structure

```
flight-booking-system/
├── admin/                  # Admin panel files
│   ├── index.php          # Admin dashboard
│   ├── flights.php        # Flight management
│   ├── bookings.php       # Booking management
│   ├── passengers.php     # Passenger management
│   ├── staff.php          # Staff management
│   └── aircraft.php       # Aircraft management
├── assets/                # Static assets
│   ├── css/
│   │   ├── style.css      # Main stylesheet
│   │   └── admin.css      # Admin panel styles
│   ├── js/
│   │   └── main.js        # JavaScript functions
│   └── images/            # Image files
├── includes/              # PHP includes
│   ├── config.php         # Configuration
│   ├── db.php             # Database connection
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   └── functions.php      # Helper functions
├── index.php              # Homepage
├── login.php              # Login page
├── register.php          # Registration page
├── search.php             # Flight search
├── booking.php            # Booking form
├── payment.php            # Payment processing
├── confirmation.php       # Booking confirmation
├── my-bookings.php        # User bookings
├── cancel-booking.php     # Cancel booking
├── logout.php             # Logout
├── database.sql           # Database schema
└── README.md              # This file
```

## 🔐 Security Features

- Password hashing using PHP's built-in functions
- Session-based authentication
- SQL injection prevention using prepared statements
- Role-based access control (Admin/Staff/User)
- Input validation and sanitization
- Transaction support for payment processing

## 🎨 Features Highlights

- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern UI**: Clean and intuitive user interface
- **Real-time Updates**: Live flight status and availability
- **Transaction Safety**: Database transactions ensure data integrity
- **Admin Dashboard**: Comprehensive statistics and management tools
- **Search Filters**: Advanced flight search with multiple filters

## 🤝 Contributing

This is a collaborative project for a database systems course. Contributions are welcome from team members following the branch strategy outlined in `GITHUB_COLLABORATION_PLAN.md`.

### Development Workflow
1. Create a feature branch from `main`
2. Make your changes
3. Commit with descriptive messages
4. Push to your branch
5. Create a Pull Request
6. Get code review approval
7. Merge to main

## 📝 Project Team

- **Course**: Enginnering WEB Based system
- **Section**: 3 
- **Institution**: ASTU (Adama Science and Technology University)

## 📄 License

This project is created for educational purposes. Please contact the project team for usage permissions.

## 🐛 Known Issues

- Payment integration is simulated (requires actual payment gateway for production)
- Email notifications are not implemented (requires SMTP configuration)

## 🔮 Future Enhancements

- Email notification system
- Real-time flight tracking integration
- Mobile app development
- Multi-language support
- Advanced analytics and reporting
- Integration with real airline APIs

## 📞 Support

For issues, questions, or suggestions, please contact the project team or create an issue in the GitHub repository.

---

**Built with ❤️ for Enginnering WEB Based system Course**

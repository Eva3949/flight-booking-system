<?php
// Database configuration
// define('DB_SERVER', 'sql300.infinityfree.com');
// define('DB_USERNAME', 'if0_41562340');
// define('DB_PASSWORD', 'Eva39499987');
// define('DB_NAME', 'if0_41562340_flight_booking_system');
// define('DB_PORT', '3306');

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'flight_booking_system');
define('DB_PORT', '3306');

// Application configuration
define('SITE_NAME', 'Flight Booking System');
define('SITE_URL', 'http://localhost/flight-booking-system');
define('ADMIN_EMAIL', 'admin@yourdomain.com');

// Session configuration
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('Africa/Addis_Ababa');
?>
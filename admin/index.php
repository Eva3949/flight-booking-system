<?php
// Fix the path to config.php
require_once '../includes/config.php';
require_once '../includes/db.php';


$pageTitle = "Admin Dashboard";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

// Get statistics
$totalPassengers = $db->query("SELECT COUNT(*) as count FROM passengers")->fetch_assoc()['count'];
$totalFlights = $db->query("SELECT COUNT(*) as count FROM flights")->fetch_assoc()['count'];
$totalBookings = $db->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$totalRevenue = $db->query("SELECT SUM(total_amount) as total FROM bookings WHERE payment_status = 'Completed'")->fetch_assoc()['total'];
$totalRevenue = $totalRevenue ? $totalRevenue : 0;

// Get recent bookings
$recentBookings = $db->query("
    SELECT b.*, f.airline_name, f.departure_time, p.name as passenger_name,
    src.code as source_code, dst.code as destination_code
    FROM bookings b
    JOIN flights f ON b.flight_id = f.flight_id
    JOIN passengers p ON b.passenger_id = p.passenger_id
    JOIN airports src ON f.source_airport_id = src.airport_id
    JOIN airports dst ON f.destination_airport_id = dst.airport_id
    ORDER BY b.booking_date DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get upcoming flights
$upcomingFlights = $db->query("
    SELECT f.*, 
    src.name as source_airport, src.code as source_code,
    dst.name as destination_airport, dst.code as destination_code,
    a.model as aircraft_model,
    (SELECT COUNT(*) FROM bookings WHERE flight_id = f.flight_id) as bookings_count
    FROM flights f
    JOIN airports src ON f.source_airport_id = src.airport_id
    JOIN airports dst ON f.destination_airport_id = dst.airport_id
    JOIN aircraft a ON f.aircraft_id = a.aircraft_id
    WHERE f.departure_time > NOW()
    ORDER BY f.departure_time ASC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <ul class="admin-menu">
            <li class="active"><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="flights.php"><i class="fas fa-plane"></i> Flights</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="passengers.php"><i class="fas fa-users"></i> Passengers</a></li>
            <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Dashboard</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Passengers</h3>
                    <p><?php echo $totalPassengers; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Flights</h3>
                    <p><?php echo $totalFlights; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Bookings</h3>
                    <p><?php echo $totalBookings; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Revenue</h3>
                    <p>Birr <?php echo number_format($totalRevenue, 2); ?></p>
                </div>
            </div>
        </div>
        
        <div class="admin-sections">
            <div class="admin-section">
                <div class="admin-section-header">
                    <h3>Recent Bookings</h3>
                    <a href="bookings.php" class="view-all">View All</a>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Passenger</th>
                                <th>Flight</th>
                                <th>Route</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentBookings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No bookings found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td>#<?php echo $booking['booking_id']; ?></td>
                                        <td><?php echo $booking['passenger_name']; ?></td>
                                        <td><?php echo $booking['airline_name']; ?></td>
                                        <td><?php echo $booking['source_code']; ?> → <?php echo $booking['destination_code']; ?></td>
                                        <td><?php echo formatDate($booking['departure_time']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower($booking['payment_status']); ?>">
                                                <?php echo $booking['payment_status']; ?>
                                            </span>
                                        </td>
                                        <td>Birr <?php echo number_format($booking['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="admin-section">
                <div class="admin-section-header">
                    <h3>Upcoming Flights</h3>
                    <a href="flights.php" class="view-all">View All</a>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Airline</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Aircraft</th>
                                <th>Bookings</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($upcomingFlights)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No upcoming flights found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($upcomingFlights as $flight): ?>
                                    <tr>
                                        <td>#<?php echo $flight['flight_id']; ?></td>
                                        <td><?php echo $flight['airline_name']; ?></td>
                                        <td><?php echo $flight['source_code']; ?> → <?php echo $flight['destination_code']; ?></td>
                                        <td><?php echo formatDateTime($flight['departure_time']); ?></td>
                                        <td><?php echo $flight['aircraft_model']; ?></td>
                                        <td><?php echo $flight['bookings_count']; ?> / <?php echo $flight['number_of_seats']; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower($flight['status']); ?>">
                                                <?php echo $flight['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

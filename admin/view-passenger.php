<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "View Passenger";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

// Check if passenger ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('passengers.php');
}

$passenger_id = (int)$_GET['id'];

// Get passenger details
$stmt = $db->prepare("SELECT * FROM passengers WHERE passenger_id = ?");
$stmt->bind_param("i", $passenger_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('passengers.php');
}

$passenger = $result->fetch_assoc();

// Get passenger's bookings
$stmt = $db->prepare("
    SELECT b.*, f.airline_name, f.departure_time, f.arrival_time,
    src.city as source_city, src.code as source_code,
    dst.city as destination_city, dst.code as destination_code
    FROM bookings b
    JOIN flights f ON b.flight_id = f.flight_id
    JOIN airports src ON f.source_airport_id = src.airport_id
    JOIN airports dst ON f.destination_airport_id = dst.airport_id
    WHERE b.passenger_id = ?
    ORDER BY b.booking_date DESC
    LIMIT 5
");
$stmt->bind_param("i", $passenger_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <ul class="admin-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="flights.php"><i class="fas fa-plane"></i> Flights</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li class="active"><a href="passengers.php"><i class="fas fa-users"></i> Passengers</a></li>
            <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Passenger Details</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <a href="passengers.php" class="admin-btn" style="background-color: #6c757d;"><i class="fas fa-arrow-left"></i> Back to Passengers</a>
            <a href="passenger-bookings.php?id=<?php echo $passenger_id; ?>" class="admin-btn"><i class="fas fa-ticket-alt"></i> View All Bookings</a>
        </div>
        
        <div class="admin-form" style="margin-bottom: 20px;">
            <h3>Personal Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 15px;">
                <div>
                    <strong>Name:</strong>
                    <p><?php echo htmlspecialchars($passenger['name']); ?></p>
                </div>
                <div>
                    <strong>Email:</strong>
                    <p><?php echo htmlspecialchars($passenger['email']); ?></p>
                </div>
                <div>
                    <strong>Phone Number:</strong>
                    <p><?php echo htmlspecialchars($passenger['phone_number']); ?></p>
                </div>
                <div>
                    <strong>Passport Number:</strong>
                    <p><?php echo $passenger['passport_number'] ? htmlspecialchars($passenger['passport_number']) : 'N/A'; ?></p>
                </div>
                <div>
                    <strong>Address:</strong>
                    <p><?php echo $passenger['address'] ? htmlspecialchars($passenger['address']) : 'N/A'; ?></p>
                </div>
                <div>
                    <strong>Registered On:</strong>
                    <p><?php echo formatDateTime($passenger['created_at']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="admin-form">
            <h3>Recent Bookings</h3>
            
            <?php if (empty($bookings)): ?>
                <p>No bookings found for this passenger.</p>
            <?php else: ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Flight</th>
                                <th>Route</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['booking_id']; ?></td>
                                    <td><?php echo $booking['airline_name']; ?></td>
                                    <td><?php echo $booking['source_code']; ?> → <?php echo $booking['destination_code']; ?></td>
                                    <td><?php echo formatDate($booking['departure_time']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower($booking['payment_status']); ?>">
                                            <?php echo $booking['payment_status']; ?>
                                        </span>
                                    </td>
                                    <td>Birr <?php echo number_format($booking['total_amount'], 2); ?></td>
                                    <td>
                                        <a href="view-booking.php?id=<?php echo $booking['booking_id']; ?>" class="admin-btn" style="padding: 5px 10px; background-color: #17a2b8;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($bookings) >= 5): ?>
                    <div style="margin-top: 15px; text-align: center;">
                        <a href="passenger-bookings.php?id=<?php echo $passenger_id; ?>" class="admin-btn">View All Bookings</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

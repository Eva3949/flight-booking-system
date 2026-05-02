<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$pageTitle = "View Booking";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('bookings.php');
}

$booking_id = (int)$_GET['id'];

// Get booking details
$booking = getBookingById($booking_id);

// If booking not found, redirect
if (!$booking) {
    redirect('bookings.php');
}

// Handle booking status update
if (isset($_POST['update_status']) && isset($_POST['status'])) {
    $status = sanitize($_POST['status']);
    
    if (in_array($status, ['Pending', 'Completed', 'Failed', 'Refunded'])) {
        // Start transaction
        $db->query("BEGIN");
        
        try {
            // Update booking status
            $stmt = $db->prepare("UPDATE bookings SET payment_status = ? WHERE booking_id = ?");
            $stmt->bind_param("si", $status, $booking_id);
            $stmt->execute();
            
            // Update payment status
            $stmt = $db->prepare("UPDATE payments SET transaction_status = ? WHERE booking_id = ?");
            $stmt->bind_param("si", $status, $booking_id);
            $stmt->execute();
            
            // If status is Refunded, update ticket status to Cancelled
            if ($status === 'Refunded') {
                $stmt = $db->prepare("UPDATE tickets SET ticket_status = 'Cancelled' WHERE booking_id = ?");
                $stmt->bind_param("i", $booking_id);
                $stmt->execute();
            }
            
            // Commit transaction
            $db->query("COMMIT");
            
            $update_success = "Booking status has been updated to $status.";
            
            // Refresh booking data
            $booking = getBookingById($booking_id);
        } catch (Exception $e) {
            // Rollback transaction
            $db->query("ROLLBACK");
            $update_error = "Failed to update booking status: " . $e->getMessage();
        }
    } else {
        $update_error = "Invalid status selected.";
    }
}
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <ul class="admin-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="flights.php"><i class="fas fa-plane"></i> Flights</a></li>
            <li class="active"><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="passengers.php"><i class="fas fa-users"></i> Passengers</a></li>
            <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Booking Details</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <?php if (isset($update_success)): ?>
            <div class="alert alert-success"><?php echo $update_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($update_error)): ?>
            <div class="alert alert-danger"><?php echo $update_error; ?></div>
        <?php endif; ?>
        
        <div style="margin-bottom: 20px;">
            <a href="bookings.php" class="admin-btn" style="background-color: #6c757d;"><i class="fas fa-arrow-left"></i> Back to Bookings</a>
            <a href="javascript:window.print();" class="admin-btn"><i class="fas fa-print"></i> Print Booking</a>
        </div>
        
        <div class="admin-form" style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Booking #<?php echo $booking_id; ?></h3>
                <span class="status-badge <?php echo strtolower($booking['payment_status']); ?>" style="font-size: 1rem; padding: 5px 15px;">
                    <?php echo $booking['payment_status']; ?>
                </span>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div>
                    <strong>Booking Date:</strong>
                    <p><?php echo formatDateTime($booking['booking_date']); ?></p>
                </div>
                <div>
                    <strong>Total Amount:</strong>
                    <p>Birr <?php echo number_format($booking['total_amount'], 2); ?></p>
                </div>
                <div>
                    <strong>Payment Method:</strong>
                    <p><?php echo isset($booking['payment']['payment_mode']) ? $booking['payment']['payment_mode'] : 'N/A'; ?></p>
                </div>
                <div>
                    <strong>Transaction ID:</strong>
                    <p><?php echo isset($booking['payment']['transaction_id']) ? $booking['payment']['transaction_id'] : 'N/A'; ?></p>
                </div>
            </div>
            
            <h4>Passenger Information</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div>
                    <strong>Name:</strong>
                    <p><?php echo $booking['passenger_name']; ?></p>
                </div>
                <div>
                    <strong>Email:</strong>
                    <p><?php echo $booking['passenger_email']; ?></p>
                </div>
            </div>
            
            <h4>Flight Information</h4>
            <div class="flight-card" style="margin-bottom: 20px;">
                <div class="flight-logo">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="flight-details">
                    <div class="flight-route">
                        <div class="flight-city">
                            <div><?php echo $booking['source_city']; ?> (<?php echo $booking['source_code']; ?>)</div>
                            <div class="flight-time"><?php echo formatTime($booking['departure_time']); ?></div>
                        </div>
                        <div class="flight-route-divider"></div>
                        <div class="flight-city">
                            <div><?php echo $booking['destination_city']; ?> (<?php echo $booking['destination_code']; ?>)</div>
                            <div class="flight-time"><?php echo formatTime($booking['arrival_time']); ?></div>
                        </div>
                    </div>
                    <div class="flight-info">
                        <div><i class="fas fa-calendar"></i> <?php echo formatDate($booking['departure_time']); ?></div>
                        <div><i class="fas fa-clock"></i> <?php echo formatDuration($booking['duration']); ?></div>
                        <div><i class="fas fa-plane"></i> <?php echo $booking['airline_name']; ?></div>
                    </div>
                </div>
            </div>
            
            <h4>Tickets</h4>
            <table class="admin-table" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Passenger Name</th>
                        <th>Seat</th>
                        <th>Class</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($booking['tickets'] as $ticket): ?>
                        <tr>
                            <td>#<?php echo $ticket['ticket_id']; ?></td>
                            <td><?php echo $ticket['passenger_name']; ?></td>
                            <td><?php echo $ticket['seat_number']; ?></td>
                            <td><?php echo $ticket['class_type']; ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($ticket['ticket_status']); ?>">
                                    <?php echo $ticket['ticket_status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h4>Update Booking Status</h4>
            <form action="view-booking.php?id=<?php echo $booking_id; ?>" method="post" style="max-width: 400px;">
                <div class="admin-form-group">
                    <label for="status">New Status</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="Pending" <?php echo $booking['payment_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Completed" <?php echo $booking['payment_status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Failed" <?php echo $booking['payment_status'] === 'Failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="Refunded" <?php echo $booking['payment_status'] === 'Refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <button type="submit" name="update_status" class="admin-btn">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

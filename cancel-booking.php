<?php
$pageTitle = "Cancel Booking";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if booking_id is provided
if (!isset($_GET['booking_id'])) {
    redirect('my-bookings.php');
}

$booking_id = (int)$_GET['booking_id'];

// Get booking details
$booking = getBookingById($booking_id);

// If booking not found or doesn't belong to the logged-in user, redirect
if (!$booking || $booking['passenger_id'] != $_SESSION['passenger_id']) {
    redirect('my-bookings.php');
}

// Check if booking is already cancelled or refunded
if ($booking['payment_status'] === 'Refunded') {
    redirect('my-bookings.php?error=already_cancelled');
}

$success = false;
$error = '';

// Process cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    // Start transaction
    $db->query("BEGIN");
    
    try {
        // Update booking status
        $stmt = $db->prepare("UPDATE bookings SET payment_status = 'Refunded' WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Update payment status
        $stmt = $db->prepare("UPDATE payments SET transaction_status = 'Refunded' WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Update ticket status
        $stmt = $db->prepare("UPDATE tickets SET ticket_status = 'Cancelled' WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Commit transaction
        $db->query("COMMIT");
        
        $success = true;
    } catch (Exception $e) {
        // Rollback transaction
        $db->query("ROLLBACK");
        $error = "Failed to cancel booking. Please try again.";
    }
}
?>

<div class="container">
    <h2>Cancel Booking</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Your booking has been successfully cancelled and a refund has been initiated.
            <p>Please allow 5-7 business days for the refund to be processed to your original payment method.</p>
            <p><a href="my-bookings.php" class="btn btn-primary">Return to My Bookings</a></p>
        </div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <p><a href="my-bookings.php" class="btn btn-primary">Return to My Bookings</a></p>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h3>Booking Details</h3>
            </div>
            <div class="card-body">
                <div class="flight-card">
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
                    <div class="flight-price">
                        <div class="price">Birr <?php echo number_format($booking['total_amount'], 2); ?></div>
                    </div>
                </div>
                
                <h4 style="margin-top: 20px;">Passengers</h4>
                <ul>
                    <?php foreach ($booking['tickets'] as $ticket): ?>
                        <li><?php echo $ticket['passenger_name']; ?> - <?php echo $ticket['class_type']; ?> Class</li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="alert alert-warning" style="margin-top: 20px;">
                    <h4><i class="fas fa-exclamation-triangle"></i> Cancellation Policy</h4>
                    <p>Please note the following cancellation terms:</p>
                    <ul>
                        <li>Cancellations made more than 24 hours before departure will receive a full refund.</li>
                        <li>Cancellations made within 24 hours of departure may be subject to a cancellation fee.</li>
                        <li>Refunds will be processed to the original payment method within 5-7 business days.</li>
                    </ul>
                </div>
                
                <form action="cancel-booking.php?booking_id=<?php echo $booking_id; ?>" method="post" onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                    <div class="form-group" style="margin-top: 20px;">
                        <button type="submit" name="confirm_cancel" class="btn btn-danger">Confirm Cancellation</button>
                        <a href="my-bookings.php" class="btn btn-secondary">Back to My Bookings</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
            <h3>SQL Transaction Example:</h3>
            <pre style="background-color: #f1f1f1; padding: 10px; border-radius: 5px; overflow-x: auto;">
// Start transaction
$db->query("BEGIN");

try {
    // Update booking status
    $stmt = $db->prepare("UPDATE bookings SET payment_status = 'Refunded' WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    
    // Update payment status
    $stmt = $db->prepare("UPDATE payments SET transaction_status = 'Refunded' WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    
    // Update ticket status
    $stmt = $db->prepare("UPDATE tickets SET ticket_status = 'Cancelled' WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    
    // Commit transaction
    $db->query("COMMIT");
    
    $success = true;
} catch (Exception $e) {
    // Rollback transaction
    $db->query("ROLLBACK");
    $error = "Failed to cancel booking. Please try again.";
}
</pre>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

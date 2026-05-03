<?php
$pageTitle = "Booking Confirmation";
require_once 'includes/header.php';  

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');  
}

// Check if booking_id is provided
if (!isset($_GET['booking_id'])) {
    redirect('index.php');
}

$booking_id = (int)$_GET['booking_id'];

// Get booking details
$booking = getBookingById($booking_id);

// If booking not found or doesn't belong to the logged-in user, redirect
if (!$booking || $booking['passenger_id'] != $_SESSION['passenger_id']) {
    redirect('index.php');
}
?>

<div class="container">
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Your booking has been confirmed! Your tickets are ready.
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Booking Details</h2>
        </div>
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 20px;">
                <div>
                    <strong>Booking Reference:</strong> #<?php echo $booking_id; ?><br>
                    <strong>Booking Date:</strong> <?php echo formatDateTime($booking['booking_date']); ?><br>
                    <strong>Payment Status:</strong> 
                    <span class="badge" style="background-color: <?php echo $booking['payment_status'] === 'Completed' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 3px 8px; border-radius: 4px;">
                        <?php echo $booking['payment_status']; ?>
                    </span>
                </div>
                <div>
                    <a href="javascript:window.print();" class="btn btn-primary"><i class="fas fa-print"></i> Print Tickets</a>
                </div>
            </div>
            
            <h3>Flight Information</h3>
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
                        <div><i class="fas fa-tag"></i> <?php echo $booking['flight_type']; ?></div>
                    </div>
                </div>
            </div>
            
            <h3 style="margin-top: 30px;">Passenger Tickets</h3>
            
            <?php foreach ($booking['tickets'] as $ticket): ?>
                <div class="ticket">
                    <div class="ticket-header">
                        <div class="ticket-airline"><?php echo $booking['airline_name']; ?></div>
                        <div class="ticket-status">Confirmed</div>
                    </div>
                    <div class="ticket-body">
                        <div class="ticket-route">
                            <div class="ticket-city">
                                <div class="ticket-city-code"><?php echo $booking['source_code']; ?></div>
                                <div class="ticket-city-name"><?php echo $booking['source_city']; ?></div>
                            </div>
                            <div class="ticket-route-divider">
                                <div class="ticket-route-line"></div>
                                <div class="ticket-route-icon">
                                    <i class="fas fa-plane"></i>
                                </div>
                            </div>
                            <div class="ticket-city">
                                <div class="ticket-city-code"><?php echo $booking['destination_code']; ?></div>
                                <div class="ticket-city-name"><?php echo $booking['destination_city']; ?></div>
                            </div>
                        </div>
                        
                        <div class="ticket-details">
                            <div class="ticket-detail">
                                <div class="ticket-detail-label">Passenger</div>
                                <div class="ticket-detail-value"><?php echo $ticket['passenger_name']; ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-detail-label">Flight</div>
                                <div class="ticket-detail-value"><?php echo $booking['airline_name']; ?> <?php echo $booking['flight_id']; ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-detail-label">Date</div>
                                <div class="ticket-detail-value"><?php echo formatDate($booking['departure_time']); ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-detail-label">Time</div>
                                <div class="ticket-detail-value"><?php echo formatTime($booking['departure_time']); ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-detail-label">Class</div>
                                <div class="ticket-detail-value"><?php echo $ticket['class_type']; ?></div>
                            </div>
                            <div class="ticket-detail">
                                <div class="ticket-detail-label">Seat</div>
                                <div class="ticket-detail-value"><?php echo $ticket['seat_number']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="ticket-footer">
                        <div class="ticket-barcode">*<?php echo str_pad($ticket['ticket_id'], 10, '0', STR_PAD_LEFT); ?>*</div>
                        <div class="ticket-note">Please arrive at the airport at least 2 hours before departure time.</div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <h3 style="margin-top: 30px;">Payment Information</h3>
            <table style="width: 100%;">
                <tr>
                    <td><strong>Payment Method:</strong></td>
                    <td><?php echo isset($booking['payment']['payment_mode']) ? $booking['payment']['payment_mode'] : 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>Transaction ID:</strong></td>
                    <td><?php echo isset($booking['payment']['transaction_id']) ? $booking['payment']['transaction_id'] : 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>Payment Date:</strong></td>
                    <td><?php echo isset($booking['payment']['payment_date']) ? formatDateTime($booking['payment']['payment_date']) : 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>Total Amount:</strong></td>
                    <td><strong>Birr <?php echo number_format($booking['total_amount'], 2); ?></strong></td>
                </tr>
            </table>
        </div>
        <div class="card-footer">
            <p>Thank you for booking with Astu Flight. We wish you a pleasant journey!</p>
            <p>For any assistance, please contact our customer support at <a href="mailto:support@skywings.com">support@skywings.com</a> or call +251 911 234 567.</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
   

<?php
$pageTitle = "My Bookings";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get passenger ID
$passenger_id = $_SESSION['passenger_id'];

// Get all bookings for this passenger
$bookings = getBookingsByPassengerId($passenger_id);
?>

<div class="container">
    <h2>My Bookings</h2>
    
    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">
            You don't have any bookings yet. <a href="search.php">Search for flights</a> to make a booking.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h3>Your Flight Bookings</h3>
            </div>
            <div class="card-body">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 12px; text-align: left;">Booking ID</th>
                            <th style="padding: 12px; text-align: left;">Flight</th>
                            <th style="padding: 12px; text-align: left;">Route</th>
                            <th style="padding: 12px; text-align: left;">Date</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Amount</th>
                            <th style="padding: 12px; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 12px;">#<?php echo $booking['booking_id']; ?></td>
                                <td style="padding: 12px;"><?php echo $booking['airline_name']; ?></td>
                                <td style="padding: 12px;"><?php echo $booking['source_code']; ?> → <?php echo $booking['destination_code']; ?></td>
                                <td style="padding: 12px;"><?php echo formatDate($booking['departure_time']); ?></td>
                                <td style="padding: 12px;">
                                    <span class="badge" style="background-color: <?php echo $booking['payment_status'] === 'Completed' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 3px 8px; border-radius: 4px;">
                                        <?php echo $booking['payment_status']; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px;">Birr <?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td style="padding: 12px;">
                                    <a href="confirmation.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

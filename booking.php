<?php
$pageTitle = "Book Flight";
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Store the current URL in session to redirect back after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login page
    redirect('login.php');
}

// Check if flight_id is provided
if (!isset($_GET['flight_id'])) {
    redirect('search.php');
}

$flight_id = (int)$_GET['flight_id'];
$passengers_count = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;

// Get flight details
$flight = getFlightById($flight_id);

// If flight not found, redirect to search page
if (!$flight) {
    redirect('search.php');
}

// Calculate total price
$total_price = $flight['base_price'] * $passengers_count;

// Process booking form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_id = $_SESSION['passenger_id'];
    $passenger_details = [];
    
    // Collect passenger details
    for ($i = 1; $i <= $passengers_count; $i++) {
        $passenger_details[] = [
            'name' => sanitize($_POST["passenger_name_$i"]),
            'seat' => generateSeatNumber(),
            'class' => sanitize($_POST["class_type_$i"])
        ];
    }
    
    // Create booking
    $booking_id = createBooking($passenger_id, $flight_id, $total_price, $passenger_details);
    
    if ($booking_id) {
        // Redirect to payment page
        redirect("payment.php?booking_id=$booking_id");
    } else {
        $error = "Failed to create booking. Please try again.";
    }
}
?>

<div class="container">
    <h2>Book Your Flight</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="booking-details">
        <div class="booking-details-col">
            <div class="card">
                <div class="card-header">
                    <h3>Flight Details</h3>
                </div>
                <div class="card-body">
                    <div class="flight-card">
                        <div class="flight-logo">
                            <i class="fas fa-plane"></i>
                        </div>
                        <div class="flight-details">
                            <div class="flight-route">
                                <div class="flight-city">
                                    <div><?php echo $flight['source_city']; ?> (<?php echo $flight['source_code']; ?>)</div>
                                    <div class="flight-time"><?php echo formatTime($flight['departure_time']); ?></div>
                                </div>
                                <div class="flight-route-divider"></div>
                                <div class="flight-city">
                                    <div><?php echo $flight['destination_city']; ?> (<?php echo $flight['destination_code']; ?>)</div>
                                    <div class="flight-time"><?php echo formatTime($flight['arrival_time']); ?></div>
                                </div>
                            </div>
                            <div class="flight-info">
                                <div><i class="fas fa-calendar"></i> <?php echo formatDate($flight['departure_time']); ?></div>
                                <div><i class="fas fa-clock"></i> <?php echo formatDuration($flight['duration']); ?></div>
                                <div><i class="fas fa-plane"></i> <?php echo $flight['airline_name']; ?></div>
                                <div><i class="fas fa-tag"></i> <?php echo $flight['flight_type']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3>Price Summary</h3>
                </div>
                <div class="card-body">
                    <table style="width: 100%;">
                        <tr>
                            <td>Base Fare (<?php echo $passengers_count; ?> passenger<?php echo $passengers_count > 1 ? 's' : ''; ?>)</td>
                            <td align="right">Birr <?php echo number_format($flight['base_price'] * $passengers_count, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Taxes & Fees</td>
                            <td align="right">Included</td>
                        </tr>
                        <tr style="font-weight: bold; font-size: 1.2rem;">
                            <td>Total</td>
                            <td align="right">Birr <?php echo number_format($total_price, 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="booking-details-col">
            <div class="card">
                <div class="card-header">
                    <h3>Passenger Information</h3>
                </div>
                <div class="card-body">
                    <form action="booking.php?flight_id=<?php echo $flight_id; ?>&passengers=<?php echo $passengers_count; ?>" method="post">
                        <?php for ($i = 1; $i <= $passengers_count; $i++): ?>
                            <div class="passenger-form">
                                <h4>Passenger <?php echo $i; ?></h4>
                                
                                <div class="form-group">
                                    <label for="passenger_name_<?php echo $i; ?>">Full Name (as on ID/Passport)</label>
                                    <input type="text" name="passenger_name_<?php echo $i; ?>" id="passenger_name_<?php echo $i; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="class_type_<?php echo $i; ?>">Class</label>
                                    <select name="class_type_<?php echo $i; ?>" id="class_type_<?php echo $i; ?>">
                                        <option value="Economy">Economy</option>
                                        <option value="Business">Business</option>
                                        <option value="First Class">First Class</option>
                                    </select>
                                </div>
                            </div>
                        <?php endfor; ?>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Proceed to Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>  

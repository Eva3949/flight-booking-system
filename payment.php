<?php
$pageTitle = "Payment";
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

// Check if payment is already completed
if ($booking['payment_status'] === 'Completed') {
    redirect("confirmation.php?booking_id=$booking_id");
}

$error = '';
$success = false;

// Process payment form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitize($_POST['payment_method']);
    
    // Generate a random transaction ID
    $transaction_id = generateTransactionId();
    
    // Update payment status
    if (updatePaymentStatus($booking_id, $payment_method, $transaction_id)) {
        // Redirect to confirmation page
        redirect("confirmation.php?booking_id=$booking_id");
    } else {
        $error = "Payment processing failed. Please try again.";
    }
}
?>

<div class="container">
    <h2>Payment</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="booking-details">
        <div class="booking-details-col">
            <div class="card">
                <div class="card-header">
                    <h3>Booking Summary</h3>
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
                    </div>
                    
                    <h4 style="margin-top: 20px;">Passengers</h4>
                    <ul>
                        <?php foreach ($booking['tickets'] as $ticket): ?>
                            <li><?php echo $ticket['passenger_name']; ?> - <?php echo $ticket['class_type']; ?> Class</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-footer">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Total Amount:</span>
                        <span style="font-size: 1.5rem; font-weight: bold; color: #0066cc;">Birr <?php echo number_format($booking['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="booking-details-col">
            <div class="card">
                <div class="card-header">
                    <h3>Payment Method</h3>
                </div>
                <div class="card-body">
                    <form action="payment.php?booking_id=<?php echo $booking_id; ?>" method="post" id="payment-form">
                        <div class="payment-methods">
                            <div class="payment-method" data-method="Credit Card">
                                <div class="payment-method-header">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Credit Card</span>
                                </div>
                                <p>Pay securely with your credit card</p>
                            </div>
                            
                            <div class="payment-method" data-method="Debit Card">
                                <div class="payment-method-header">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Debit Card</span>
                                </div>
                                <p>Pay directly from your bank account</p>
                            </div>
                            
                            <div class="payment-method" data-method="PayPal">
                                <div class="payment-method-header">
                                    <i class="fab fa-paypal"></i>
                                    <span>PayPal</span>
                                </div>
                                <p>Pay with your PayPal account</p>
                            </div>
                            
                            <div class="payment-method" data-method="Bank Transfer">
                                <div class="payment-method-header">
                                    <i class="fas fa-university"></i>
                                    <span>Bank Transfer</span>
                                </div>
                                <p>Pay via bank transfer</p>
                            </div>
                        </div>
                        
                        <input type="hidden" name="payment_method" id="payment_method" value="Credit Card">
                        
                        <div id="credit-card-form" class="payment-form-details">
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="expiry_date">Expiry Date</label>
                                        <input type="text" id="expiry_date" placeholder="MM/YY" maxlength="5">
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="cvv">CVV</label>
                                        <input type="text" id="cvv" placeholder="123" maxlength="3">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="card_name">Name on Card</label>
                                <input type="text" id="card_name" placeholder="John Doe">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Pay Birr <?php echo number_format($booking['total_amount'], 2); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    const paymentMethodInput = document.getElementById('payment_method');
    
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove active class from all methods
            paymentMethods.forEach(m => m.classList.remove('active'));
            
            // Add active class to selected method
            this.classList.add('active');
            
            // Update hidden input value
            paymentMethodInput.value = this.getAttribute('data-method');
        });
    });
    
    // Set first payment method as active by default
    paymentMethods[0].classList.add('active');
    
    // Format credit card number with spaces
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            e.target.value = formattedValue;
        });
    }
    
    // Format expiry date with slash
    const expiryDateInput = document.getElementById('expiry_date');
    if (expiryDateInput) {
        expiryDateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            e.target.value = value;
        });
    }
    
    // Form submission (for demo purposes, we're not actually validating)
    const paymentForm = document.getElementById('payment-form');
    paymentForm.addEventListener('submit', function(e) {
        // In a real application, you would validate the form and process the payment
        // For this demo, we'll just submit the form
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

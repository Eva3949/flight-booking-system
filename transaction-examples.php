<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

/**
 * This file demonstrates how to use explicit SQL transaction statements
 * in the Flight Booking System
 */

// Example 1: Creating a booking with explicit SQL transactions
function createBookingWithSqlTransactions($passengerId, $flightId, $totalAmount, $passengers) {
    global $db;
    
    // Start transaction with explicit SQL
    $db->query("BEGIN");
    
    try {
        // Create booking
        $stmt = $db->prepare("INSERT INTO bookings (passenger_id, flight_id, total_amount) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $passengerId, $flightId, $totalAmount);
        $stmt->execute();
        
        $bookingId = $db->getLastId();
        
        // Create tickets for each passenger
        foreach ($passengers as $passenger) {
            $stmt = $db->prepare("INSERT INTO tickets (booking_id, seat_number, class_type, passenger_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $bookingId, $passenger['seat'], $passenger['class'], $passenger['name']);
            $stmt->execute();
        }
        
        // Create payment record
        $stmt = $db->prepare("INSERT INTO payments (booking_id, payment_amount) VALUES (?, ?)");
        $stmt->bind_param("id", $bookingId, $totalAmount);
        $stmt->execute();
        
        // Commit transaction with explicit SQL
        $db->query("COMMIT");
        
        return $bookingId;
    } catch (Exception $e) {
        // Rollback transaction with explicit SQL
        $db->query("ROLLBACK");
        return false;
    }
}

// Example 2: Processing a payment with explicit SQL transactions
function processPaymentWithSqlTransactions($bookingId, $paymentMode, $transactionId) {
    global $db;
    
    // Start transaction
    $db->query("BEGIN");
    
    try {
        // Update payment record
        $stmt = $db->prepare("UPDATE payments SET payment_mode = ?, transaction_status = 'Completed', transaction_id = ? WHERE booking_id = ?");
        $stmt->bind_param("ssi", $paymentMode, $transactionId, $bookingId);
        $stmt->execute();
        
        // Update booking status
        $stmt = $db->prepare("UPDATE bookings SET payment_status = 'Completed' WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        
        // Commit transaction
        $db->query("COMMIT");
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction
        $db->query("ROLLBACK");
        return false;
    }
}

// Example 3: Cancelling a booking with explicit SQL transactions
function cancelBookingWithSqlTransactions($bookingId) {
    global $db;
    
    // Start transaction
    $db->query("BEGIN");
    
    try {
        // Update booking status
        $stmt = $db->prepare("UPDATE bookings SET payment_status = 'Refunded' WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        
        // Update payment status
        $stmt = $db->prepare("UPDATE payments SET transaction_status = 'Refunded' WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        
        // Update ticket status
        $stmt = $db->prepare("UPDATE tickets SET ticket_status = 'Cancelled' WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        
        // Commit transaction
        $db->query("COMMIT");
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction
        $db->query("ROLLBACK");
        return false;
    }
}

// Example 4: Transferring a seat with explicit SQL transactions and SAVEPOINT
function transferSeatWithSqlTransactions($ticketId, $newPassengerName) {
    global $db;
    
    // Start transaction
    $db->query("BEGIN");
    
    try {
        // Get current ticket details
        $stmt = $db->prepare("SELECT booking_id, seat_number, class_type FROM tickets WHERE ticket_id = ?");
        $stmt->bind_param("i", $ticketId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Ticket not found, rollback and return
            $db->query("ROLLBACK");
            return false;
        }
        
        $ticket = $result->fetch_assoc();
        
        // Create a savepoint before updating the ticket
        $db->query("SAVEPOINT before_update");
        
        // Update the ticket with new passenger name
        $stmt = $db->prepare("UPDATE tickets SET passenger_name = ? WHERE ticket_id = ?");
        $stmt->bind_param("si", $newPassengerName, $ticketId);
        $updateResult = $stmt->execute();
        
        if (!$updateResult) {
            // If update fails, rollback to savepoint
            $db->query("ROLLBACK TO SAVEPOINT before_update");
            
            // Try a different approach - create a new ticket and cancel the old one
            $db->query("SAVEPOINT try_alternative");
            
            // Mark the old ticket as cancelled
            $stmt = $db->prepare("UPDATE tickets SET ticket_status = 'Cancelled' WHERE ticket_id = ?");
            $stmt->bind_param("i", $ticketId);
            $stmt->execute();
            
            // Create a new ticket for the new passenger
            $stmt = $db->prepare("INSERT INTO tickets (booking_id, seat_number, class_type, passenger_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $ticket['booking_id'], $ticket['seat_number'], $ticket['class_type'], $newPassengerName);
            $insertResult = $stmt->execute();
            
            if (!$insertResult) {
                // If insert fails, rollback to savepoint
                $db->query("ROLLBACK TO SAVEPOINT try_alternative");
                $db->query("ROLLBACK");
                return false;
            }
        }
        
        // Log the transfer in a transfer history table (if it existed)
        // This is just for demonstration purposes
        /*
        $stmt = $db->prepare("INSERT INTO ticket_transfers (ticket_id, old_passenger, new_passenger, transfer_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $ticketId, $oldPassengerName, $newPassengerName);
        $stmt->execute();
        */
        
        // Commit transaction
        $db->query("COMMIT");
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction
        $db->query("ROLLBACK");
        return false;
    }
}

// Example 5: Batch updating flight statuses with explicit SQL transactions
function updateFlightStatusesWithSqlTransactions($flightIds, $newStatus) {
    global $db;
    
    // Start transaction
    $db->query("BEGIN");
    
    try {
        // Create a prepared statement for updating flight status
        $stmt = $db->prepare("UPDATE flights SET status = ? WHERE flight_id = ?");
        
        // Update each flight
        $successCount = 0;
        foreach ($flightIds as $flightId) {
            $stmt->bind_param("si", $newStatus, $flightId);
            if ($stmt->execute()) {
                $successCount++;
            } else {
                // If any update fails, rollback the entire transaction
                $db->query("ROLLBACK");
                return false;
            }
        }
        
        // Commit transaction
        $db->query("COMMIT");
        
        return $successCount;
    } catch (Exception $e) {
        // Rollback transaction
        $db->query("ROLLBACK");
        return false;
    }
}

// Example usage demonstration
function demonstrateTransactions() {
    echo "<h1>SQL Transaction Examples in Flight Booking System</h1>";
    
    echo "<h2>Example 1: Creating a Booking with SQL Transactions</h2>";
    echo "<pre>
function createBookingWithSqlTransactions(\$passengerId, \$flightId, \$totalAmount, \$passengers) {
    global \$db;
    
    // Start transaction with explicit SQL
    \$db->query(\"BEGIN\");
    
    try {
        // Create booking
        \$stmt = \$db->prepare(\"INSERT INTO bookings (passenger_id, flight_id, total_amount) VALUES (?, ?, ?)\");
        \$stmt->bind_param(\"iid\", \$passengerId, \$flightId, \$totalAmount);
        \$stmt->execute();
        
        \$bookingId = \$db->getLastId();
        
        // Create tickets for each passenger
        foreach (\$passengers as \$passenger) {
            \$stmt = \$db->prepare(\"INSERT INTO tickets (booking_id, seat_number, class_type, passenger_name) VALUES (?, ?, ?, ?)\");
            \$stmt->bind_param(\"isss\", \$bookingId, \$passenger['seat'], \$passenger['class'], \$passenger['name']);
            \$stmt->execute();
        }
        
        // Create payment record
        \$stmt = \$db->prepare(\"INSERT INTO payments (booking_id, payment_amount) VALUES (?, ?)\");
        \$stmt->bind_param(\"id\", \$bookingId, \$totalAmount);
        \$stmt->execute();
        
        // Commit transaction with explicit SQL
        \$db->query(\"COMMIT\");
        
        return \$bookingId;
    } catch (Exception \$e) {
        // Rollback transaction with explicit SQL
        \$db->query(\"ROLLBACK\");
        return false;
    }
}
</pre>";

    echo "<h2>Example 2: Processing a Payment with SQL Transactions</h2>";
    echo "<pre>
function processPaymentWithSqlTransactions(\$bookingId, \$paymentMode, \$transactionId) {
    global \$db;
    
    // Start transaction
    \$db->query(\"BEGIN\");
    
    try {
        // Update payment record
        \$stmt = \$db->prepare(\"UPDATE payments SET payment_mode = ?, transaction_status = 'Completed', transaction_id = ? WHERE booking_id = ?\");
        \$stmt->bind_param(\"ssi\", \$paymentMode, \$transactionId, \$bookingId);
        \$stmt->execute();
        
        // Update booking status
        \$stmt = \$db->prepare(\"UPDATE bookings SET payment_status = 'Completed' WHERE booking_id = ?\");
        \$stmt->bind_param(\"i\", \$bookingId);
        \$stmt->execute();
        
        // Commit transaction
        \$db->query(\"COMMIT\");
        
        return true;
    } catch (Exception \$e) {
        // Rollback transaction
        \$db->query(\"ROLLBACK\");
        return false;
    }
}
</pre>";

    echo "<h2>Example 3: Cancelling a Booking with SQL Transactions</h2>";
    echo "<pre>
function cancelBookingWithSqlTransactions(\$bookingId) {
    global \$db;
    
    // Start transaction
    \$db->query(\"BEGIN\");
    
    try {
        // Update booking status
        \$stmt = \$db->prepare(\"UPDATE bookings SET payment_status = 'Refunded' WHERE booking_id = ?\");
        \$stmt->bind_param(\"i\", \$bookingId);
        \$stmt->execute();
        
        // Update payment status
        \$stmt = \$db->prepare(\"UPDATE payments SET transaction_status = 'Refunded' WHERE booking_id = ?\");
        \$stmt->bind_param(\"i\", \$bookingId);
        \$stmt->execute();
        
        // Update ticket status
        \$stmt = \$db->prepare(\"UPDATE tickets SET ticket_status = 'Cancelled' WHERE booking_id = ?\");
        \$stmt->bind_param(\"i\", \$bookingId);
        \$stmt->execute();
        
        // Commit transaction
        \$db->query(\"COMMIT\");
        
        return true;
    } catch (Exception \$e) {
        // Rollback transaction
        \$db->query(\"ROLLBACK\");
        return false;
    }
}
</pre>";

    echo "<h2>Example 4: Transferring a Seat with SQL Transactions and SAVEPOINT</h2>";
    echo "<pre>
function transferSeatWithSqlTransactions(\$ticketId, \$newPassengerName) {
    global \$db;
    
    // Start transaction
    \$db->query(\"BEGIN\");
    
    try {
        // Get current ticket details
        \$stmt = \$db->prepare(\"SELECT booking_id, seat_number, class_type FROM tickets WHERE ticket_id = ?\");
        \$stmt->bind_param(\"i\", \$ticketId);
        \$stmt->execute();
        \$result = \$stmt->get_result();
        
        if (\$result->num_rows === 0) {
            // Ticket not found, rollback and return
            \$db->query(\"ROLLBACK\");
            return false;
        }
        
        \$ticket = \$result->fetch_assoc();
        
        // Create a savepoint before updating the ticket
        \$db->query(\"SAVEPOINT before_update\");
        
        // Update the ticket with new passenger name
        \$stmt = \$db->prepare(\"UPDATE tickets SET passenger_name = ? WHERE ticket_id = ?\");
        \$stmt->bind_param(\"si\", \$newPassengerName, \$ticketId);
        \$updateResult = \$stmt->execute();
        
        if (!\$updateResult) {
            // If update fails, rollback to savepoint
            \$db->query(\"ROLLBACK TO SAVEPOINT before_update\");
            
            // Try a different approach - create a new ticket and cancel the old one
            \$db->query(\"SAVEPOINT try_alternative\");
            
            // Mark the old ticket as cancelled
            \$stmt = \$db->prepare(\"UPDATE tickets SET ticket_status = 'Cancelled' WHERE ticket_id = ?\");
            \$stmt->bind_param(\"i\", \$ticketId);
            \$stmt->execute();
            
            // Create a new ticket for the new passenger
            \$stmt = \$db->prepare(\"INSERT INTO tickets (booking_id, seat_number, class_type, passenger_name) VALUES (?, ?, ?, ?)\");
            \$stmt->bind_param(\"isss\", \$ticket['booking_id'], \$ticket['seat_number'], \$ticket['class_type'], \$newPassengerName);
            \$insertResult = \$stmt->execute();
            
            if (!\$insertResult) {
                // If insert fails, rollback to savepoint
                \$db->query(\"ROLLBACK TO SAVEPOINT try_alternative\");
                \$db->query(\"ROLLBACK\");
                return false;
            }
        }
        
        // Commit transaction
        \$db->query(\"COMMIT\");
        
        return true;
    } catch (Exception \$e) {
        // Rollback transaction
        \$db->query(\"ROLLBACK\");
        return false;
    }
}
</pre>";

    echo "<h2>Example 5: Batch Updating Flight Statuses with SQL Transactions</h2>";
    echo "<pre>
function updateFlightStatusesWithSqlTransactions(\$flightIds, \$newStatus) {
    global \$db;
    
    // Start transaction
    \$db->query(\"BEGIN\");
    
    try {
        // Create a prepared statement for updating flight status
        \$stmt = \$db->prepare(\"UPDATE flights SET status = ? WHERE flight_id = ?\");
        
        // Update each flight
        \$successCount = 0;
        foreach (\$flightIds as \$flightId) {
            \$stmt->bind_param(\"si\", \$newStatus, \$flightId);
            if (\$stmt->execute()) {
                \$successCount++;
            } else {
                // If any update fails, rollback the entire transaction
                \$db->query(\"ROLLBACK\");
                return false;
            }
        }
        
        // Commit transaction
        \$db->query(\"COMMIT\");
        
        return \$successCount;
    } catch (Exception \$e) {
        // Rollback transaction
        \$db->query(\"ROLLBACK\");
        return false;
    }
}
</pre>";

    echo "<h2>Implementing These Functions in the Flight Booking System</h2>";
    echo "<p>To use these functions in the existing system, you would replace the current implementations with these versions that use explicit SQL transaction statements.</p>";
    
    echo "<p>For example, in <code>includes/functions.php</code>, you would replace the current <code>createBooking</code> function with <code>createBookingWithSqlTransactions</code>.</p>";
    
    echo "<p>Similarly, you would replace <code>updatePaymentStatus</code> with <code>processPaymentWithSqlTransactions</code>.</p>";
    
    echo "<p>The other functions (<code>cancelBookingWithSqlTransactions</code>, <code>transferSeatWithSqlTransactions</code>, and <code>updateFlightStatusesWithSqlTransactions</code>) would be added as new functionality to the system.</p>";
}

// Run the demonstration
demonstrateTransactions();
?>

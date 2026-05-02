<?php
require_once 'db.php';

// Function to sanitize input data
function sanitize($data) {
    global $db;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $db->escapeString($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['passenger_id']);
}

// Function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['staff_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to get all flights
function getAllFlights() {
    global $db;
    $sql = "SELECT f.*, 
            src.name as source_airport, src.city as source_city, src.country as source_country, src.code as source_code,
            dst.name as destination_airport, dst.city as destination_city, dst.country as destination_country, dst.code as destination_code,
            a.model as aircraft_model
            FROM flights f
            JOIN airports src ON f.source_airport_id = src.airport_id
            JOIN airports dst ON f.destination_airport_id = dst.airport_id
            JOIN aircraft a ON f.aircraft_id = a.aircraft_id
            WHERE f.departure_time > NOW()
            ORDER BY f.departure_time ASC";
    
    $result = $db->query($sql);
    $flights = [];
    
    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
    }
    
    return $flights;
}

// Function to search flights
function searchFlights($source, $destination, $date) {
    global $db;
    $source = $db->escapeString($source);
    $destination = $db->escapeString($destination);
    $date = $db->escapeString($date);
    
    $sql = "SELECT f.*, 
            src.name as source_airport, src.city as source_city, src.country as source_country, src.code as source_code,
            dst.name as destination_airport, dst.city as destination_city, dst.country as destination_country, dst.code as destination_code,
            a.model as aircraft_model
            FROM flights f
            JOIN airports src ON f.source_airport_id = src.airport_id
            JOIN airports dst ON f.destination_airport_id = dst.airport_id
            JOIN aircraft a ON f.aircraft_id = a.aircraft_id
            WHERE src.airport_id = '$source' 
            AND dst.airport_id = '$destination' 
            AND DATE(f.departure_time) = '$date'
            AND f.status = 'Scheduled'
            ORDER BY f.departure_time ASC";
    
    $result = $db->query($sql);
    $flights = [];
    
    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
    }
    
    return $flights;
}

// Function to get flight by ID
function getFlightById($flightId) {
    global $db;
    $flightId = $db->escapeString($flightId);
    
    $sql = "SELECT f.*, 
            src.name as source_airport, src.city as source_city, src.country as source_country, src.code as source_code,
            dst.name as destination_airport, dst.city as destination_city, dst.country as destination_country, dst.code as destination_code,
            a.model as aircraft_model
            FROM flights f
            JOIN airports src ON f.source_airport_id = src.airport_id
            JOIN airports dst ON f.destination_airport_id = dst.airport_id
            JOIN aircraft a ON f.aircraft_id = a.aircraft_id
            WHERE f.flight_id = '$flightId'";
    
    $result = $db->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Function to get all airports
function getAllAirports() {
    global $db;
    $sql = "SELECT * FROM airports ORDER BY name ASC";
    $result = $db->query($sql);
    $airports = [];
    
    while ($row = $result->fetch_assoc()) {
        $airports[] = $row;
    }
    
    return $airports;
}

// Function to create booking with explicit SQL transactions
function createBooking($passengerId, $flightId, $totalAmount, $passengers) {
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

// Function to get booking by ID
function getBookingById($bookingId) {
    global $db;
    $bookingId = $db->escapeString($bookingId);
    
    $sql = "SELECT b.*, f.*, 
            src.name as source_airport, src.city as source_city, src.code as source_code,
            dst.name as destination_airport, dst.city as destination_city, dst.code as destination_code,
            p.name as passenger_name, p.email as passenger_email
            FROM bookings b
            JOIN flights f ON b.flight_id = f.flight_id
            JOIN airports src ON f.source_airport_id = src.airport_id
            JOIN airports dst ON f.destination_airport_id = dst.airport_id
            JOIN passengers p ON b.passenger_id = p.passenger_id
            WHERE b.booking_id = '$bookingId'";
    
    $result = $db->query($sql);
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        
        // Get tickets for this booking
        $sql = "SELECT * FROM tickets WHERE booking_id = '$bookingId'";
        $ticketResult = $db->query($sql);
        $tickets = [];
        
        while ($ticket = $ticketResult->fetch_assoc()) {
            $tickets[] = $ticket;
        }
        
        $booking['tickets'] = $tickets;
        
        // Get payment info
        $sql = "SELECT * FROM payments WHERE booking_id = '$bookingId'";
        $paymentResult = $db->query($sql);
        
        if ($paymentResult->num_rows > 0) {
            $booking['payment'] = $paymentResult->fetch_assoc();
        }
        
        return $booking;
    }
    
    return null;
}

// Function to get bookings by passenger ID
function getBookingsByPassengerId($passengerId) {
    global $db;
    $passengerId = $db->escapeString($passengerId);
    
    $sql = "SELECT b.*, f.airline_name, f.departure_time, f.arrival_time,
            src.city as source_city, src.code as source_code,
            dst.city as destination_city, dst.code as destination_code
            FROM bookings b
            JOIN flights f ON b.flight_id = f.flight_id
            JOIN airports src ON f.source_airport_id = src.airport_id
            JOIN airports dst ON f.destination_airport_id = dst.airport_id
            WHERE b.passenger_id = '$passengerId'
            ORDER BY b.booking_date DESC";
    
    $result = $db->query($sql);
    $bookings = [];
    
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    return $bookings;
}

// Function to update payment status with explicit SQL transactions
function updatePaymentStatus($bookingId, $paymentMode, $transactionId) {
    global $db;
    
    // Start transaction
    $db->query("BEGIN");
    
    try {
        // Update payment record
        $stmt = $db->prepare("UPDATE payments SET payment_mode = ?, transaction_status = 'Completed', transaction_id = ? WHERE booking_id = ?");
        $stmt->bind_param("ssi", $paymentMode, $transactionId, $bookingId);
        $result = $stmt->execute();
        
        if (!$result) {
            // If payment update fails, rollback
            $db->query("ROLLBACK");
            return false;
        }
        
        // Update booking payment status
        $stmt = $db->prepare("UPDATE bookings SET payment_status = 'Completed' WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $result = $stmt->execute();
        
        if (!$result) {
            // If booking update fails, rollback
            $db->query("ROLLBACK");
            return false;
        }
        
        // Commit transaction
        $db->query("COMMIT");
        
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->query("ROLLBACK");
        return false;
    }
}

// Function to cancel a booking with explicit SQL transactions
function cancelBooking($bookingId) {
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

// Function to format date and time
function formatDateTime($dateTime) {
    return date("M d, Y h:i A", strtotime($dateTime));
}

// Function to format date
function formatDate($date) {
    return date("M d, Y", strtotime($date));
}

// Function to format time
function formatTime($time) {
    return date("h:i A", strtotime($time));
}

// Function to calculate flight duration in hours and minutes
function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . "h " . $mins . "m";
}

// Function to generate a random seat number
function generateSeatNumber() {
    $row = chr(rand(65, 75)); // A to K
    $number = rand(1, 30);
    return $row . $number;
}

// Function to generate a random transaction ID
function generateTransactionId() {
    return 'TXN' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
}
?>

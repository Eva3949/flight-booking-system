<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "Batch Update Flights";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$success = false;
$error = '';
$updatedCount = 0;

// Get all flights for selection
$allFlights = $db->query("
    SELECT f.flight_id, f.airline_name, f.departure_time, f.status,
    src.code as source_code, dst.code as destination_code
    FROM flights f
    JOIN airports src ON f.source_airport_id = src.airport_id
    JOIN airports dst ON f.destination_airport_id = dst.airport_id
    WHERE f.departure_time > NOW()
    ORDER BY f.departure_time ASC
")->fetch_all(MYSQLI_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_flights'])) {
    // Get selected flights and new status
    $selectedFlights = isset($_POST['selected_flights']) ? $_POST['selected_flights'] : [];
    $newStatus = sanitize($_POST['new_status']);
    
    if (empty($selectedFlights)) {
        $error = "Please select at least one flight to update.";
    } elseif (empty($newStatus)) {
        $error = "Please select a new status.";
    } else {
        // Start transaction
        $db->query("BEGIN");
        
        try {
            // Create a prepared statement for updating flight status
            $stmt = $db->prepare("UPDATE flights SET status = ? WHERE flight_id = ?");
            
            // Update each flight
            $successCount = 0;
            foreach ($selectedFlights as $flightId) {
                $stmt->bind_param("si", $newStatus, $flightId);
                if ($stmt->execute()) {
                    $successCount++;
                } else {
                    // If any update fails, rollback the entire transaction
                    $db->query("ROLLBACK");
                    $error = "Failed to update flight #$flightId. Transaction rolled back.";
                    break;
                }
            }
            
            if (empty($error)) {
                // Commit transaction
                $db->query("COMMIT");
                $success = true;
                $updatedCount = $successCount;
            }
        } catch (Exception $e) {
            // Rollback transaction
            $db->query("ROLLBACK");
            $error = "An error occurred: " . $e->getMessage();
        }
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
            <li class="active"><a href="flights.php"><i class="fas fa-plane"></i> Flights</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="passengers.php"><i class="fas fa-users"></i> Passengers</a></li>
            <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Batch Update Flight Statuses</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Successfully updated <?php echo $updatedCount; ?> flight(s) to status: <?php echo htmlspecialchars($_POST['new_status']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-form">
            <p>This page demonstrates using SQL transactions to update multiple flights at once. All updates will be committed together, or rolled back if any update fails.</p>
            
            <form action="batch-update-flights.php" method="post">
                <div class="admin-form-group">
                    <label>Select Flights to Update:</label>
                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px;">
                        <?php if (empty($allFlights)): ?>
                            <p>No upcoming flights found.</p>
                        <?php else: ?>
                            <?php foreach ($allFlights as $flight): ?>
                                <div style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                                    <label>
                                        <input type="checkbox" name="selected_flights[]" value="<?php echo $flight['flight_id']; ?>">
                                        Flight #<?php echo $flight['flight_id']; ?> - 
                                        <?php echo $flight['airline_name']; ?> - 
                                        <?php echo $flight['source_code']; ?> → <?php echo $flight['destination_code']; ?> - 
                                        <?php echo formatDateTime($flight['departure_time']); ?> - 
                                        Current Status: <span class="status-badge <?php echo strtolower($flight['status']); ?>"><?php echo $flight['status']; ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="admin-form-group">
                    <label for="new_status">New Status:</label>
                    <select id="new_status" name="new_status" required>
                        <option value="">Select New Status</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Delayed">Delayed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                
                <div class="admin-form-group">
                    <button type="submit" name="update_flights" class="admin-btn">Update Selected Flights</button>
                    <a href="flights.php" class="admin-btn" style="background-color: #6c757d;">Back to Flights</a>
                </div>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                <h3>SQL Transaction Code Example:</h3>
                <pre style="background-color: #f1f1f1; padding: 10px; border-radius: 5px; overflow-x: auto;">
// Start transaction
$db->query("BEGIN");

try {
    // Create a prepared statement for updating flight status
    $stmt = $db->prepare("UPDATE flights SET status = ? WHERE flight_id = ?");
    
    // Update each flight
    $successCount = 0;
    foreach ($selectedFlights as $flightId) {
        $stmt->bind_param("si", $newStatus, $flightId);
        if ($stmt->execute()) {
            $successCount++;
        } else {
            // If any update fails, rollback the entire transaction
            $db->query("ROLLBACK");
            $error = "Failed to update flight #$flightId. Transaction rolled back.";
            break;
        }
    }
    
    if (empty($error)) {
        // Commit transaction
        $db->query("COMMIT");
        $success = true;
        $updatedCount = $successCount;
    }
} catch (Exception $e) {
    // Rollback transaction
    $db->query("ROLLBACK");
    $error = "An error occurred: " . $e->getMessage();
}
</pre>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

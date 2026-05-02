<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "Add New Flight";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$success = false;
$error = '';

// Get all airports
$airports = getAllAirports();

// Get all aircraft
$aircraft = $db->query("SELECT * FROM aircraft")->fetch_all(MYSQLI_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $airline = sanitize($_POST['airline']);
    $source = (int)$_POST['source'];
    $destination = (int)$_POST['destination'];
    $departure_date = sanitize($_POST['departure_date']);
    $departure_time = sanitize($_POST['departure_time']);
    $duration = (int)$_POST['duration'];
    $flight_type = sanitize($_POST['flight_type']);
    $seats = (int)$_POST['seats'];
    $aircraft_id = (int)$_POST['aircraft_id'];
    $base_price = (float)$_POST['base_price'];
    $status = sanitize($_POST['status']);
    
    // Validate form data
    if (empty($airline) || empty($source) || empty($destination) || empty($departure_date) || 
        empty($departure_time) || empty($duration) || empty($flight_type) || 
        empty($seats) || empty($aircraft_id) || empty($base_price) || empty($status)) {
        $error = "All fields are required";
    } elseif ($source === $destination) {
        $error = "Source and destination airports cannot be the same";
    } else {
        // Combine date and time
        $departure_datetime = $departure_date . ' ' . $departure_time . ':00';
        
        // Calculate arrival time
        $arrival_datetime = date('Y-m-d H:i:s', strtotime($departure_datetime) + ($duration * 60));
        
        // Insert flight
        $stmt = $db->prepare("INSERT INTO flights (airline_name, source_airport_id, destination_airport_id, 
                              departure_time, arrival_time, duration, flight_type, number_of_seats, 
                              aircraft_id, base_price, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("siissisiids", 
            $airline, 
            $source, 
            $destination, 
            $departure_datetime, 
            $arrival_datetime, 
            $duration, 
            $flight_type, 
            $seats, 
            $aircraft_id, 
            $base_price, 
            $status
        );
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Failed to add flight: " . $stmt->error;
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
            <h2>Add New Flight</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Flight added successfully! <a href="flights.php">View all flights</a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-form">
            <form action="add-flight.php" method="post">
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="airline">Airline Name</label>
                            <input type="text" id="airline" name="airline" value="<?php echo isset($_POST['airline']) ? $_POST['airline'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="flight_type">Flight Type</label>
                            <select id="flight_type" name="flight_type" required>
                                <option value="">Select Flight Type</option>
                                <option value="Domestic" <?php echo (isset($_POST['flight_type']) && $_POST['flight_type'] === 'Domestic') ? 'selected' : ''; ?>>Domestic</option>
                                <option value="International" <?php echo (isset($_POST['flight_type']) && $_POST['flight_type'] === 'International') ? 'selected' : ''; ?>>International</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="source">Source Airport</label>
                            <select id="source" name="source" required>
                                <option value="">Select Source Airport</option>
                                <?php foreach ($airports as $airport): ?>
                                    <option value="<?php echo $airport['airport_id']; ?>" <?php echo (isset($_POST['source']) && $_POST['source'] == $airport['airport_id']) ? 'selected' : ''; ?>>
                                        <?php echo $airport['city']; ?> (<?php echo $airport['code']; ?>) - <?php echo $airport['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="destination">Destination Airport</label>
                            <select id="destination" name="destination" required>
                                <option value="">Select Destination Airport</option>
                                <?php foreach ($airports as $airport): ?>
                                    <option value="<?php echo $airport['airport_id']; ?>" <?php echo (isset($_POST['destination']) && $_POST['destination'] == $airport['airport_id']) ? 'selected' : ''; ?>>
                                        <?php echo $airport['city']; ?> (<?php echo $airport['code']; ?>) - <?php echo $airport['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="departure_date">Departure Date</label>
                            <input type="date" id="departure_date" name="departure_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($_POST['departure_date']) ? $_POST['departure_date'] : date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="departure_time">Departure Time</label>
                            <input type="time" id="departure_time" name="departure_time" value="<?php echo isset($_POST['departure_time']) ? $_POST['departure_time'] : '12:00'; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="duration">Duration (minutes)</label>
                            <input type="number" id="duration" name="duration" min="30" max="1440" value="<?php echo isset($_POST['duration']) ? $_POST['duration'] : '120'; ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="seats">Number of Seats</label>
                            <input type="number" id="seats" name="seats" min="1" max="500" value="<?php echo isset($_POST['seats']) ? $_POST['seats'] : '150'; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="aircraft_id">Aircraft</label>
                            <select id="aircraft_id" name="aircraft_id" required>
                                <option value="">Select Aircraft</option>
                                <?php foreach ($aircraft as $plane): ?>
                                    <option value="<?php echo $plane['aircraft_id']; ?>" <?php echo (isset($_POST['aircraft_id']) && $_POST['aircraft_id'] == $plane['aircraft_id']) ? 'selected' : ''; ?>>
                                        <?php echo $plane['model']; ?> (<?php echo $plane['manufacturer']; ?>) - Capacity: <?php echo $plane['capacity']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="base_price">Base Price ($)</label>
                            <input type="number" id="base_price" name="base_price" min="50" step="0.01" value="<?php echo isset($_POST['base_price']) ? $_POST['base_price'] : '500'; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="Scheduled" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="Delayed" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Delayed') ? 'selected' : ''; ?>>Delayed</option>
                                <option value="Cancelled" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-group">
                    <button type="submit" class="admin-btn">Add Flight</button>
                    <a href="flights.php" class="admin-btn" style="background-color: #6c757d;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

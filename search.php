<?php
$pageTitle = "Search Flights";
require_once 'includes/header.php';

$flights = [];
$searched = false;
$error = '';

// Check if search form is submitted
if (isset($_GET['source']) && isset($_GET['destination']) && isset($_GET['departure_date'])) {
    $source = sanitize($_GET['source']);
    $destination = sanitize($_GET['destination']);
    $departure_date = sanitize($_GET['departure_date']);
    $passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;
    
    // Validate search parameters
    if (!empty($source) && !empty($destination) && !empty($departure_date)) {
        // Check if source and destination are the same
        if ($source == $destination) {
            $error = "Source and destination airports cannot be the same.";
        } else {
            $flights = searchFlights($source, $destination, $departure_date);
            $searched = true;
        }
    }
}

// Get all airports for the search form
$airports = getAllAirports();

// Get flight count for the next 30 days (for debugging)
$flightCount = $db->query("SELECT COUNT(*) as count FROM flights WHERE departure_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
?>

<div class="container">
    <div class="search-form">
        <h2>Search Flights</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="search.php" method="get">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="source">From</label>
                        <select name="source" id="source" required>
                            <option value="">Select Departure Airport</option>
                            <?php foreach ($airports as $airport): ?>
                                <option value="<?php echo $airport['airport_id']; ?>" <?php echo (isset($_GET['source']) && $_GET['source'] == $airport['airport_id']) ? 'selected' : ''; ?>>
                                    <?php echo $airport['city']; ?> (<?php echo $airport['code']; ?>) - <?php echo $airport['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="destination">To</label>
                        <select name="destination" id="destination" required>
                            <option value="">Select Arrival Airport</option>
                            <?php foreach ($airports as $airport): ?>
                                <option value="<?php echo $airport['airport_id']; ?>" <?php echo (isset($_GET['destination']) && $_GET['destination'] == $airport['airport_id']) ? 'selected' : ''; ?>>
                                    <?php echo $airport['city']; ?> (<?php echo $airport['code']; ?>) - <?php echo $airport['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="departure_date">Departure Date</label>
                        <input type="date" name="departure_date" id="departure_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($_GET['departure_date']) ? $_GET['departure_date'] : date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="passengers">Passengers</label>
                        <select name="passengers" id="passengers">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo (isset($_GET['passengers']) && $_GET['passengers'] == $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> Passenger<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Search Flights</button>
            </div>
        </form>
    </div>
    
    <?php if ($searched): ?>
        <div class="search-results">
            <h2>Search Results</h2>
            
            <?php if (empty($flights)): ?>
                <div class="alert alert-info">
                    No flights found for the selected criteria. Please try different dates or destinations.
                    <p>There are currently <?php echo $flightCount; ?> flights scheduled for the next 30 days.</p>
                    <p>If you're an administrator, you can <a href="add-flights.php">add more flights</a> to the system.</p>
                </div>
            <?php else: ?>
                <div class="card">
                    <?php foreach ($flights as $flight): ?>
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
                            <div class="flight-price">
                                <div class="price">Birr <?php echo number_format($flight['base_price'], 2); ?></div>
                                <a href="booking.php?flight_id=<?php echo $flight['flight_id']; ?>&passengers=<?php echo $passengers; ?>" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

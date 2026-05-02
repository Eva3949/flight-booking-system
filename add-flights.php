<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Function to add more flights to the database
function addMoreFlights() {
    global $db;
    
    // Get all airports
    $airports = $db->query("SELECT airport_id FROM airports")->fetch_all(MYSQLI_ASSOC);
    
    // Get all aircraft
    $aircraft = $db->query("SELECT aircraft_id FROM aircraft")->fetch_all(MYSQLI_ASSOC);
    
    // Airlines
    $airlines = ['Ethiopian Airlines', 'Emirates', 'EgyptAir', 'Kenya Airways', 'Turkish Airlines', 'Qatar Airways'];
    
    // Flight types
    $flightTypes = ['Domestic', 'International'];
    
    // Status options
    $statusOptions = ['Scheduled', 'Delayed', 'Cancelled'];
    
    // Generate flights for the next 3 months
    $startDate = new DateTime();
    $endDate = new DateTime('+3 months');
    
    $interval = new DateInterval('P1D'); // 1 day interval
    $dateRange = new DatePeriod($startDate, $interval, $endDate);
    
    $flightsAdded = 0;
    $errors = [];
    
    foreach ($dateRange as $date) {
        // Add 2-4 flights per day
        $flightsPerDay = rand(2, 4);
        
        for ($i = 0; $i < $flightsPerDay; $i++) {
            // Randomly select source and destination airports (ensure they're different)
            do {
                $sourceIndex = array_rand($airports);
                $destIndex = array_rand($airports);
            } while ($sourceIndex === $destIndex);
            
            $sourceAirportId = $airports[$sourceIndex]['airport_id'];
            $destAirportId = $airports[$destIndex]['airport_id'];
            
            // Random airline
            $airline = $airlines[array_rand($airlines)];
            
            // Random aircraft
            $aircraftId = $aircraft[array_rand($aircraft)]['aircraft_id'];
            
            // Random flight type
            $flightType = $flightTypes[array_rand($flightTypes)];
            
            // Random status (mostly scheduled)
            $status = (rand(1, 10) > 8) ? $statusOptions[array_rand($statusOptions)] : 'Scheduled';
            
            // Random departure time on the given date
            $hour = rand(0, 23);
            $minute = rand(0, 11) * 5; // 5-minute intervals
            $departureTime = $date->format('Y-m-d') . " " . sprintf("%02d:%02d:00", $hour, $minute);
            
            // Random duration between 1 and 10 hours (in minutes)
            $duration = rand(60, 600);
            
            // Calculate arrival time
            $arrivalDateTime = new DateTime($departureTime);
            $arrivalDateTime->add(new DateInterval('PT' . $duration . 'M'));
            $arrivalTime = $arrivalDateTime->format('Y-m-d H:i:s');
            
            // Random number of seats
            $seats = rand(100, 300);
            
            // Random base price between $200 and $1500
            $basePrice = rand(200, 1500);
            
            // Insert the flight
            $sql = "INSERT INTO flights (airline_name, source_airport_id, destination_airport_id, 
                    departure_time, arrival_time, duration, flight_type, number_of_seats, 
                    aircraft_id, base_price, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("siissisiids", 
                $airline, 
                $sourceAirportId, 
                $destAirportId, 
                $departureTime, 
                $arrivalTime, 
                $duration, 
                $flightType, 
                $seats, 
                $aircraftId, 
                $basePrice, 
                $status
            );
            
            if ($stmt->execute()) {
                $flightsAdded++;
            } else {
                $errors[] = "Error adding flight: " . $stmt->error;
            }
        }
    }
    
    return [
        'success' => count($errors) === 0,
        'flights_added' => $flightsAdded,
        'errors' => $errors
    ];
}

// Check if the script is being run directly
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    $result = addMoreFlights();
    
    if ($result['success']) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;'>";
        echo "<h2>Success!</h2>";
        echo "<p>Successfully added {$result['flights_added']} new flights to the database.</p>";
        echo "<p><a href='index.php'>Return to homepage</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;'>";
        echo "<h2>Error</h2>";
        echo "<p>Added {$result['flights_added']} flights, but encountered errors:</p>";
        echo "<ul>";
        foreach ($result['errors'] as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        echo "<p><a href='index.php'>Return to homepage</a></p>";
        echo "</div>";
    }
}
?>

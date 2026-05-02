<?php
// Test database connection
$host = 'sql300.infinityfree.com';
$port = '3306';

echo "Testing connection to: $host:$port\n";

// Test if hostname resolves
$ip = gethostbyname($host);
if ($ip == $host) {
    echo "ERROR: Hostname '$host' cannot be resolved!\n";
} else {
    echo "Hostname resolves to IP: $ip\n";
}

// Test port connection
$connection = @fsockopen($host, $port, $errno, $errstr, 5);
if (!$connection) {
    echo "ERROR: Cannot connect to $host:$port - $errstr ($errno)\n";
} else {
    echo "Successfully connected to $host:$port\n";
    fclose($connection);
}

// Try database connection
try {
    $mysqli = new mysqli($host, 'if0_41562340', 'Eva39499987', 'if0_41562340_flight_booking_system', $port);
    if ($mysqli->connect_error) {
        echo "Database connection failed: " . $mysqli->connect_error . "\n";
    } else {
        echo "Database connection successful!\n";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
}
?>

<?php
// Try to get IP address for the hostname
$host = 'sql300.infinityfree.com';
$ips = gethostbynamel($host);

if ($ips === false) {
    echo "Cannot resolve hostname: $host\n";
    echo "This confirms the DNS issue.\n";
} else {
    echo "Hostname $host resolves to:\n";
    foreach ($ips as $ip) {
        echo "- $ip\n";
    }
    
    // Test connection with IP
    $first_ip = $ips[0];
    echo "\nTesting connection with IP: $first_ip\n";
    
    try {
        $mysqli = new mysqli($first_ip, 'if0_41562340', 'Eva39499987', 'if0_41562340_flight_booking_system', 3306);
        if ($mysqli->connect_error) {
            echo "Connection failed with IP: " . $mysqli->connect_error . "\n";
        } else {
            echo "SUCCESS: Connection works with IP address!\n";
            echo "You can use: define('DB_SERVER', '$first_ip');\n";
            $mysqli->close();
        }
    } catch (Exception $e) {
        echo "Connection error with IP: " . $e->getMessage() . "\n";
    }
}
?>

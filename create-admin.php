<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// This script will create a new admin user

// Set the details for the new admin
$name = 'Admin User';
$role = 'Administrator';
$contactNumber = '+251911111111';
$email = 'admin@skywings.com';
$password = 'admin123';

// Create a proper password hash
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Check if the email already exists
$checkStmt = $db->prepare("SELECT staff_id FROM staff WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;">';
    echo '<h2>Error</h2>';
    echo '<p>A staff member with email <strong>' . htmlspecialchars($email) . '</strong> already exists.</p>';
    echo '<p>You can use the reset-staff-password.php script to reset their password instead.</p>';
    echo '</div>';
} else {
    // Insert the new admin
    $insertStmt = $db->prepare("INSERT INTO staff (name, role, contact_number, email, password) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->bind_param("sssss", $name, $role, $contactNumber, $email, $passwordHash);
    
    if ($insertStmt->execute()) {
        echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;">';
        echo '<h2>Admin Created Successfully!</h2>';
        echo '<p>A new admin user has been created with the following details:</p>';
        echo '<ul>';
        echo '<li><strong>Name:</strong> ' . htmlspecialchars($name) . '</li>';
        echo '<li><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>';
        echo '<li><strong>Password:</strong> ' . htmlspecialchars($password) . '</li>';
        echo '</ul>';
        echo '<p>You can now login with these credentials. Make sure to check the "Login as Staff/Admin" checkbox on the login page.</p>';
        echo '<p><a href="login.php">Go to Login Page</a></p>';
        echo '</div>';
    } else {
        echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;">';
        echo '<h2>Error</h2>';
        echo '<p>Failed to create admin: ' . $insertStmt->error . '</p>';
        echo '</div>';
    }
    
    // Close the statement
    $insertStmt->close();
}

// Close the check statement
$checkStmt->close();
?>

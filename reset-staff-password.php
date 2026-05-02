<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// This script will reset the password for a staff member
// It creates a proper password hash that will work with the login system

// Set the email of the staff member you want to update
$staffEmail = 'john.doe@airline.com';

// Set the new password
$newPassword = 'admin123';

// Create a proper password hash
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the staff record
$stmt = $db->prepare("UPDATE staff SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $passwordHash, $staffEmail);

if ($stmt->execute()) {
    echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;">';
    echo '<h2>Password Reset Successful!</h2>';
    echo '<p>The password for staff member with email <strong>' . htmlspecialchars($staffEmail) . '</strong> has been reset to: <strong>' . htmlspecialchars($newPassword) . '</strong></p>';
    echo '<p>You can now login with these credentials. Make sure to check the "Login as Staff/Admin" checkbox on the login page.</p>';
    echo '<p><a href="login.php">Go to Login Page</a></p>';
    echo '</div>';
} else {
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px; border-radius: 5px;">';
    echo '<h2>Error</h2>';
    echo '<p>Failed to reset password: ' . $stmt->error . '</p>';
    echo '</div>';
}

// Close the statement
$stmt->close();
?>

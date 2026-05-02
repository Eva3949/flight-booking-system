<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "Add New Staff";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$success = false;
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitize($_POST['name']);
    $role = sanitize($_POST['role']);
    $email = sanitize($_POST['email']);
    $contact = sanitize($_POST['contact']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate form data
    if (empty($name) || empty($role) || empty($email) || empty($contact) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if email already exists
        $stmt = $db->prepare("SELECT * FROM staff WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists. Please use a different email.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert staff into database
            $stmt = $db->prepare("INSERT INTO staff (name, role, contact_number, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $role, $contact, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Failed to add staff member: " . $stmt->error;
            }
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
            <li><a href="flights.php"><i class="fas fa-plane"></i> Flights</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="passengers.php"><i class="fas fa-users"></i> Passengers</a></li>
            <li class="active"><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Add New Staff Member</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Staff member added successfully! <a href="staff.php">View all staff</a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-form">
            <form action="add-staff.php" method="post">
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="Administrator" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Administrator') ? 'selected' : ''; ?>>Administrator</option>
                                <option value="Pilot" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Pilot') ? 'selected' : ''; ?>>Pilot</option>
                                <option value="Co-Pilot" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Co-Pilot') ? 'selected' : ''; ?>>Co-Pilot</option>
                                <option value="Flight Attendant" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Flight Attendant') ? 'selected' : ''; ?>>Flight Attendant</option>
                                <option value="Ground Staff" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Ground Staff') ? 'selected' : ''; ?>>Ground Staff</option>
                                <option value="Customer Service" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Customer Service') ? 'selected' : ''; ?>>Customer Service</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="contact">Contact Number</label>
                            <input type="text" id="contact" name="contact" value="<?php echo isset($_POST['contact']) ? $_POST['contact'] : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-group">
                    <button type="submit" class="admin-btn">Add Staff Member</button>
                    <a href="staff.php" class="admin-btn" style="background-color: #6c757d;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

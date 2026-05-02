<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "Edit Staff";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

// Check if staff ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('staff.php');
}

$staff_id = (int)$_GET['id'];

// Get staff details
$stmt = $db->prepare("SELECT * FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('staff.php');
}

$staff = $result->fetch_assoc();

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
    
    // Validate form data
    if (empty($name) || empty($role) || empty($email) || empty($contact)) {
        $error = "Name, role, email, and contact are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email already exists (excluding current staff)
        $stmt = $db->prepare("SELECT * FROM staff WHERE email = ? AND staff_id != ?");
        $stmt->bind_param("si", $email, $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists. Please use a different email.";
        } else {
            // Update staff in database
            if (empty($password)) {
                // Update without changing password
                $stmt = $db->prepare("UPDATE staff SET name = ?, role = ?, contact_number = ?, email = ? WHERE staff_id = ?");
                $stmt->bind_param("ssssi", $name, $role, $contact, $email, $staff_id);
            } else {
                // Update with new password
                if (strlen($password) < 6) {
                    $error = "Password must be at least 6 characters long";
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $db->prepare("UPDATE staff SET name = ?, role = ?, contact_number = ?, email = ?, password = ? WHERE staff_id = ?");
                    $stmt->bind_param("sssssi", $name, $role, $contact, $email, $hashed_password, $staff_id);
                }
            }
            
            if (empty($error) && $stmt->execute()) {
                $success = true;
                
                // Update session variables if editing own account
                if ($staff_id == $_SESSION['staff_id']) {
                    $_SESSION['staff_name'] = $name;
                    $_SESSION['staff_role'] = $role;
                }
                
                // Refresh staff data
                $stmt = $db->prepare("SELECT * FROM staff WHERE staff_id = ?");
                $stmt->bind_param("i", $staff_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $staff = $result->fetch_assoc();
            } else if (empty($error)) {
                $error = "Failed to update staff member: " . $stmt->error;
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
            <h2>Edit Staff Member</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Staff member updated successfully!
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-form">
            <form action="edit-staff.php?id=<?php echo $staff_id; ?>" method="post">
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($staff['name']); ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="Administrator" <?php echo ($staff['role'] === 'Administrator') ? 'selected' : ''; ?>>Administrator</option>
                                <option value="Pilot" <?php echo ($staff['role'] === 'Pilot') ? 'selected' : ''; ?>>Pilot</option>
                                <option value="Co-Pilot" <?php echo ($staff['role'] === 'Co-Pilot') ? 'selected' : ''; ?>>Co-Pilot</option>
                                <option value="Flight Attendant" <?php echo ($staff['role'] === 'Flight Attendant') ? 'selected' : ''; ?>>Flight Attendant</option>
                                <option value="Ground Staff" <?php echo ($staff['role'] === 'Ground Staff') ? 'selected' : ''; ?>>Ground Staff</option>
                                <option value="Customer Service" <?php echo ($staff['role'] === 'Customer Service') ? 'selected' : ''; ?>>Customer Service</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="contact">Contact Number</label>
                            <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($staff['contact_number']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="password">New Password (leave blank to keep current password)</label>
                            <input type="password" id="password" name="password">
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-group">
                    <button type="submit" class="admin-btn">Update Staff Member</button>
                    <a href="staff.php" class="admin-btn" style="background-color: #6c757d;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

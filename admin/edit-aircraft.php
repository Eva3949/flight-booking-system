<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "Edit Aircraft";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

// Check if aircraft ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('aircraft.php');
}

$aircraft_id = (int)$_GET['id'];

// Get aircraft details
$stmt = $db->prepare("SELECT * FROM aircraft WHERE aircraft_id = ?");
$stmt->bind_param("i", $aircraft_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('aircraft.php');
}

$aircraft = $result->fetch_assoc();

$success = false;
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $model = sanitize($_POST['model']);
    $manufacturer = sanitize($_POST['manufacturer']);
    $capacity = (int)$_POST['capacity'];
    $status = sanitize($_POST['status']);
    
    // Validate form data
    if (empty($model) || empty($manufacturer) || empty($capacity) || empty($status)) {
        $error = "All fields are required";
    } elseif ($capacity <= 0) {
        $error = "Capacity must be greater than 0";
    } else {
        // Update aircraft in database
        $stmt = $db->prepare("UPDATE aircraft SET model = ?, manufacturer = ?, capacity = ?, maintenance_status = ? WHERE aircraft_id = ?");
        $stmt->bind_param("ssisi", $model, $manufacturer, $capacity, $status, $aircraft_id);
        
        if ($stmt->execute()) {
            $success = true;
            
            // Refresh aircraft data
            $stmt = $db->prepare("SELECT * FROM aircraft WHERE aircraft_id = ?");
            $stmt->bind_param("i", $aircraft_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $aircraft = $result->fetch_assoc();
        } else {
            $error = "Failed to update aircraft: " . $stmt->error;
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
            <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li class="active"><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Edit Aircraft</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Aircraft updated successfully!
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-form">
            <form action="edit-aircraft.php?id=<?php echo $aircraft_id; ?>" method="post">
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="model">Aircraft Model</label>
                            <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($aircraft['model']); ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="manufacturer">Manufacturer</label>
                            <input type="text" id="manufacturer" name="manufacturer" value="<?php echo htmlspecialchars($aircraft['manufacturer']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-row">
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="capacity">Seating Capacity</label>
                            <input type="number" id="capacity" name="capacity" min="1" max="1000" value="<?php echo $aircraft['capacity']; ?>" required>
                        </div>
                    </div>
                    <div class="admin-form-col">
                        <div class="admin-form-group">
                            <label for="status">Maintenance Status</label>
                            <select id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Operational" <?php echo ($aircraft['maintenance_status'] === 'Operational') ? 'selected' : ''; ?>>Operational</option>
                                <option value="Maintenance" <?php echo ($aircraft['maintenance_status'] === 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="Grounded" <?php echo ($aircraft['maintenance_status'] === 'Grounded') ? 'selected' : ''; ?>>Grounded</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="admin-form-group">
                    <button type="submit" class="admin-btn">Update Aircraft</button>
                    <a href="aircraft.php" class="admin-btn" style="background-color: #6c757d;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

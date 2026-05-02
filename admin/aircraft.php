<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "Manage Aircraft";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

// Handle aircraft deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $aircraft_id = (int)$_GET['delete'];
    
    // Check if aircraft is assigned to any flights
    $flights = $db->query("SELECT COUNT(*) as count FROM flights WHERE aircraft_id = $aircraft_id")->fetch_assoc()['count'];
    
    if ($flights > 0) {
        $delete_error = "Cannot delete aircraft #$aircraft_id because it is assigned to $flights flight(s).";
    } else {
        // Delete aircraft
        if ($db->query("DELETE FROM aircraft WHERE aircraft_id = $aircraft_id")) {
            $delete_success = "Aircraft #$aircraft_id has been deleted successfully.";
        } else {
            $delete_error = "Failed to delete aircraft #$aircraft_id.";
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE (
        model LIKE '%$search%' OR 
        manufacturer LIKE '%$search%' OR 
        maintenance_status LIKE '%$search%'
    )";
}

// Get total aircraft count
$total_aircraft = $db->query("SELECT COUNT(*) as count FROM aircraft $search_condition")->fetch_assoc()['count'];
$total_pages = ceil($total_aircraft / $limit);

// Get aircraft
$aircraft = $db->query("
    SELECT a.*, 
    (SELECT COUNT(*) FROM flights WHERE aircraft_id = a.aircraft_id) as flight_count
    FROM aircraft a 
    $search_condition
    ORDER BY manufacturer, model ASC
    LIMIT $offset, $limit
")->fetch_all(MYSQLI_ASSOC);
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
            <h2>Manage Aircraft</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <?php if (isset($delete_success)): ?>
            <div class="alert alert-success"><?php echo $delete_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($delete_error)): ?>
            <div class="alert alert-danger"><?php echo $delete_error; ?></div>
        <?php endif; ?>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <a href="add-aircraft.php" class="admin-btn"><i class="fas fa-plus"></i> Add New Aircraft</a>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <form action="aircraft.php" method="get" style="display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Search aircraft..." value="<?php echo $search; ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <button type="submit" class="admin-btn" style="padding: 8px 15px;">Search</button>
                
                <?php if (!empty($search)): ?>
                    <a href="aircraft.php" class="admin-btn" style="padding: 8px 15px; background-color: #6c757d;">Clear</a>
                <?php endif; ?>
            </form>
            
            <div>
                <span>Total Aircraft: <?php echo $total_aircraft; ?></span>
            </div>
        </div>
        
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Model</th>
                        <th>Manufacturer</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Flights</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aircraft)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No aircraft found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($aircraft as $plane): ?>
                            <tr>
                                <td>#<?php echo $plane['aircraft_id']; ?></td>
                                <td><?php echo $plane['model']; ?></td>
                                <td><?php echo $plane['manufacturer']; ?></td>
                                <td><?php echo $plane['capacity']; ?> seats</td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($plane['maintenance_status']); ?>">
                                        <?php echo $plane['maintenance_status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $plane['flight_count']; ?></td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="edit-aircraft.php?id=<?php echo $plane['aircraft_id']; ?>" class="admin-btn" style="padding: 5px 10px; background-color: #28a745;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($plane['flight_count'] == 0): ?>
                                            <a href="aircraft.php?delete=<?php echo $plane['aircraft_id']; ?>" class="admin-btn admin-btn-danger" style="padding: 5px 10px;" onclick="return confirm('Are you sure you want to delete this aircraft?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div style="margin-top: 20px; display: flex; justify-content: center;">
                <div style="display: flex; gap: 5px;">
                    <?php if ($page > 1): ?>
                        <a href="aircraft.php?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>" class="admin-btn" style="padding: 5px 10px;">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="aircraft.php?page=<?php echo $i; ?>&search=<?php echo $search; ?>" class="admin-btn" style="padding: 5px 10px; <?php echo $i === $page ? 'background-color: #2c3e50;' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="aircraft.php?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>" class="admin-btn" style="padding: 5px 10px;">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

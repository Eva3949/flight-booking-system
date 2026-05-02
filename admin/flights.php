<?php

require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "Manage Flights";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

// Handle flight deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $flight_id = (int)$_GET['delete'];
    
    // Check if flight has bookings
    $bookings = $db->query("SELECT COUNT(*) as count FROM bookings WHERE flight_id = $flight_id")->fetch_assoc()['count'];
    
    if ($bookings > 0) {
        $delete_error = "Cannot delete flight #$flight_id because it has existing bookings.";
    } else {
        // Delete flight
        if ($db->query("DELETE FROM flights WHERE flight_id = $flight_id")) {
            $delete_success = "Flight #$flight_id has been deleted successfully.";
        } else {
            $delete_error = "Failed to delete flight #$flight_id.";
        }
    }
}

// Handle flight status update
if (isset($_GET['update_status']) && is_numeric($_GET['update_status']) && isset($_GET['status'])) {
    $flight_id = (int)$_GET['update_status'];
    $status = sanitize($_GET['status']);
    
    if (in_array($status, ['Scheduled', 'Delayed', 'Cancelled', 'Completed'])) {
        if ($db->query("UPDATE flights SET status = '$status' WHERE flight_id = $flight_id")) {
            $update_success = "Flight #$flight_id status has been updated to $status.";
        } else {
            $update_error = "Failed to update flight #$flight_id status.";
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
    $search_condition = "AND (
        f.airline_name LIKE '%$search%' OR 
        src.city LIKE '%$search%' OR 
        dst.city LIKE '%$search%' OR 
        src.code LIKE '%$search%' OR 
        dst.code LIKE '%$search%'
    )";
}

// Filter by status
$status_filter = isset($_GET['status_filter']) ? sanitize($_GET['status_filter']) : '';
$status_condition = '';
if (!empty($status_filter)) {
    $status_condition = "AND f.status = '$status_filter'";
}

// Get total flights count
$total_flights = $db->query("
    SELECT COUNT(*) as count 
    FROM flights f
    JOIN airports src ON f.source_airport_id = src.airport_id
    JOIN airports dst ON f.destination_airport_id = dst.airport_id
    WHERE 1=1 $search_condition $status_condition
")->fetch_assoc()['count'];

$total_pages = ceil($total_flights / $limit);

// Get flights
$flights = $db->query("
    SELECT f.*, 
    src.name as source_airport, src.city as source_city, src.code as source_code,
    dst.name as destination_airport, dst.city as destination_city, dst.code as destination_code,
    a.model as aircraft_model,
    (SELECT COUNT(*) FROM bookings WHERE flight_id = f.flight_id) as bookings_count
    FROM flights f
    JOIN airports src ON f.source_airport_id = src.airport_id
    JOIN airports dst ON f.destination_airport_id = dst.airport_id
    JOIN aircraft a ON f.aircraft_id = a.aircraft_id
    WHERE 1=1 $search_condition $status_condition
    ORDER BY f.departure_time DESC
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
            <li class="active"><a href="flights.php"><i class="fas fa-plane"></i> Flights</a></li>
            <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="passengers.php"><i class="fas fa-users"></i> Passengers</a></li>
            <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Manage Flights</h2>
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
        
        <?php if (isset($update_success)): ?>
            <div class="alert alert-success"><?php echo $update_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($update_error)): ?>
            <div class="alert alert-danger"><?php echo $update_error; ?></div>
        <?php endif; ?>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <a href="add-flight.php" class="admin-btn"><i class="fas fa-plus"></i> Add New Flight</a>
            <a href="../add-flights.php" class="admin-btn admin-btn-success"><i class="fas fa-plus"></i> Generate Multiple Flights</a>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <form action="flights.php" method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search flights..." value="<?php echo $search; ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                
                <select name="status_filter" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">All Statuses</option>
                    <option value="Scheduled" <?php echo $status_filter === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="Delayed" <?php echo $status_filter === 'Delayed' ? 'selected' : ''; ?>>Delayed</option>
                    <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
                
                <button type="submit" class="admin-btn" style="padding: 8px 15px;">Filter</button>
                
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="flights.php" class="admin-btn" style="padding: 8px 15px; background-color: #6c757d;">Clear Filters</a>
                <?php endif; ?>
            </form>
            
            <div>
                <span>Total Flights: <?php echo $total_flights; ?></span>
            </div>
        </div>
        
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Airline</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th>Duration</th>
                        <th>Aircraft</th>
                        <th>Price</th>
                        <th>Bookings</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($flights)): ?>
                        <tr>
                            <td colspan="10" class="text-center">No flights found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($flights as $flight): ?>
                            <tr>
                                <td>#<?php echo $flight['flight_id']; ?></td>
                                <td><?php echo $flight['airline_name']; ?></td>
                                <td><?php echo $flight['source_code']; ?> → <?php echo $flight['destination_code']; ?></td>
                                <td><?php echo formatDateTime($flight['departure_time']); ?></td>
                                <td><?php echo formatDuration($flight['duration']); ?></td>
                                <td><?php echo $flight['aircraft_model']; ?></td>
                                <td>Birr <?php echo number_format($flight['base_price'], 2); ?></td>
                                <td><?php echo $flight['bookings_count']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($flight['status']); ?>">
                                        <?php echo $flight['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <div class="dropdown" style="position: relative; display: inline-block;">
                                            <button class="admin-btn" style="padding: 5px 10px; background-color: #6c757d;">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-content" style="display: none; position: absolute; background-color: #f9f9f9; min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; right: 0;">
                                                <a href="flights.php?update_status=<?php echo $flight['flight_id']; ?>&status=Scheduled" style="color: #000; padding: 12px 16px; text-decoration: none; display: block;">Mark as Scheduled</a>
                                                <a href="flights.php?update_status=<?php echo $flight['flight_id']; ?>&status=Delayed" style="color: #000; padding: 12px 16px; text-decoration: none; display: block;">Mark as Delayed</a>
                                                <a href="flights.php?update_status=<?php echo $flight['flight_id']; ?>&status=Cancelled" style="color: #000; padding: 12px 16px; text-decoration: none; display: block;">Mark as Cancelled</a>
                                                <a href="flights.php?update_status=<?php echo $flight['flight_id']; ?>&status=Completed" style="color: #000; padding: 12px 16px; text-decoration: none; display: block;">Mark as Completed</a>
                                            </div>
                                        </div>
                                        
                                        <?php if ($flight['bookings_count'] == 0): ?>
                                            <a href="flights.php?delete=<?php echo $flight['flight_id']; ?>" class="admin-btn admin-btn-danger" style="padding: 5px 10px;" onclick="return confirm('Are you sure you want to delete this flight?');">
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
                        <a href="flights.php?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&status_filter=<?php echo $status_filter; ?>" class="admin-btn" style="padding: 5px 10px;">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="flights.php?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status_filter=<?php echo $status_filter; ?>" class="admin-btn" style="padding: 5px 10px; <?php echo $i === $page ? 'background-color: #2c3e50;' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="flights.php?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&status_filter=<?php echo $status_filter; ?>" class="admin-btn" style="padding: 5px 10px;">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown functionality
    const dropdownButtons = document.querySelectorAll('.dropdown button');
    
    dropdownButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const content = this.nextElementSibling;
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                if (dropdown !== content) {
                    dropdown.style.display = 'none';
                }
            });
            
            // Toggle current dropdown
            content.style.display = content.style.display === 'block' ? 'none' : 'block';
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$pageTitle = "Manage Bookings";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

// Handle booking status update
if (isset($_GET['update_status']) && is_numeric($_GET['update_status']) && isset($_GET['status'])) {
    $booking_id = (int)$_GET['update_status'];
    $status = sanitize($_GET['status']);
    
    if (in_array($status, ['Pending', 'Completed', 'Failed', 'Refunded'])) {
        if ($db->query("UPDATE bookings SET payment_status = '$status' WHERE booking_id = $booking_id")) {
            // Also update payment status
            $db->query("UPDATE payments SET transaction_status = '$status' WHERE booking_id = $booking_id");
            $update_success = "Booking #$booking_id status has been updated to $status.";
        } else {
            $update_error = "Failed to update booking #$booking_id status.";
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
        p.name LIKE '%$search%' OR 
        p.email LIKE '%$search%' OR 
        f.airline_name LIKE '%$search%' OR
        src.code LIKE '%$search%' OR
        dst.code LIKE '%$search%'
    )";
}

// Filter by status
$status_filter = isset($_GET['status_filter']) ? sanitize($_GET['status_filter']) : '';
$status_condition = '';
if (!empty($status_filter)) {
    $status_condition = "AND b.payment_status = '$status_filter'";
}

// Get total bookings count
$total_bookings = $db->query("
    SELECT COUNT(*) as count 
    FROM bookings b
    JOIN passengers p ON b.passenger_id = p.passenger_id
    JOIN flights f ON b.flight_id = f.flight_id
    JOIN airports src ON f.source_airport_id = src.airport_id
    JOIN airports dst ON f.destination_airport_id = dst.airport_id
    WHERE 1=1 $search_condition $status_condition
")->fetch_assoc()['count'];

$total_pages = ceil($total_bookings / $limit);

// Get bookings
$bookings = $db->query("
    SELECT b.*, 
    p.name as passenger_name, p.email as passenger_email,
    f.airline_name, f.departure_time, f.arrival_time,
    src.city as source_city, src.code as source_code,
    dst.city as destination_city, dst.code as destination_code,
    (SELECT COUNT(*) FROM tickets WHERE booking_id = b.booking_id) as tickets_count
    FROM bookings b
    JOIN passengers p ON b.passenger_id = p.passenger_id
    JOIN flights f ON b.flight_id = f.flight_id
    JOIN airports src ON f.source_airport_id = src.airport_id
    JOIN airports dst ON f.destination_airport_id = dst.airport_id
    WHERE 1=1 $search_condition $status_condition
    ORDER BY b.booking_date DESC
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
            <li class="active"><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
            <li><a href="passengers.php"><i class="fas fa-users"></i> Passengers</a></li>
            <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Manage Bookings</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <?php if (isset($update_success)): ?>
            <div class="alert alert-success"><?php echo $update_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($update_error)): ?>
            <div class="alert alert-danger"><?php echo $update_error; ?></div>
        <?php endif; ?>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <form action="bookings.php" method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search bookings..." value="<?php echo $search; ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                
                <select name="status_filter" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Failed" <?php echo $status_filter === 'Failed' ? 'selected' : ''; ?>>Failed</option>
                    <option value="Refunded" <?php echo $status_filter === 'Refunded' ? 'selected' : ''; ?>>Refunded</option>
                </select>
                
                <button type="submit" class="admin-btn" style="padding: 8px 15px;">Filter</button>
                
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="bookings.php" class="admin-btn" style="padding: 8px 15px; background-color: #6c757d;">Clear Filters</a>
                <?php endif; ?>
            </form>
            
            <div>
                <span>Total Bookings: <?php echo $total_bookings; ?></span>
            </div>
        </div>
        
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Passenger</th>
                        <th>Flight</th>
                        <th>Route</th>
                        <th>Date</th>
                        <th>Tickets</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No bookings found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['booking_id']; ?></td>
                                <td>
                                    <?php echo $booking['passenger_name']; ?><br>
                                    <small><?php echo $booking['passenger_email']; ?></small>
                                </td>
                                <td><?php echo $booking['airline_name']; ?></td>
                                <td><?php echo $booking['source_code']; ?> → <?php echo $booking['destination_code']; ?></td>
                                <td><?php echo formatDate($booking['departure_time']); ?></td>
                                <td><?php echo $booking['tickets_count']; ?></td>
                                <td>Birr <?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($booking['payment_status']); ?>">
                                        <?php echo $booking['payment_status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="view-booking.php?id=<?php echo $booking['booking_id']; ?>" class="admin-btn" style="padding: 5px 10px; background-color: #17a2b8;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <div class="dropdown" style="position: relative; display: inline-block;">
                                            <button class="admin-btn" style="padding: 5px 10px; background-color: #6c757d;">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-content" style="display: none; position: absolute; background-color: #f9f9f9; min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; right: 0;">
                                                <a href="bookings.php?update_status=<?php echo $booking['booking_id']; ?>&status=Pending" style="color: #000; padding: 12px 16px; text-decoration: none; display: block;">Mark as Pending</a>
                                                <a href="bookings.php?update_status=<?php echo $booking['booking_id']; ?>&status=Completed" style="color: #000; padding: 12px 16px; text-decoration: none; display: block;">Mark as Completed</a>
                                                <a href="bookings.php?update_status=<?php echo $booking['booking_id']; ?>&status=Failed" style="color: #000; padding: 12px 16px; text-decoration: none; display: block;">Mark as Failed</a>
                                                <a href="bookings.php?update_status=<?php echo $booking['booking_id']; ?>&status=Refunded" style="color: #000; padding: 12px 16px; text-decoration: none; display: block;">Mark as Refunded</a>
                                            </div>
                                        </div>
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
                        <a href="bookings.php?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&status_filter=<?php echo $status_filter; ?>" class="admin-btn" style="padding: 5px 10px;">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="bookings.php?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status_filter=<?php echo $status_filter; ?>" class="admin-btn" style="padding: 5px 10px; <?php echo $i === $page ? 'background-color: #2c3e50;' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="bookings.php?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&status_filter=<?php echo $status_filter; ?>" class="admin-btn" style="padding: 5px 10px;">Next</a>
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

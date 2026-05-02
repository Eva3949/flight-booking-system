<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "Manage Passengers";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
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
        name LIKE '%$search%' OR 
        email LIKE '%$search%' OR 
        phone_number LIKE '%$search%' OR
        passport_number LIKE '%$search%'
    )";
}

// Get total passengers count
$total_passengers = $db->query("SELECT COUNT(*) as count FROM passengers $search_condition")->fetch_assoc()['count'];
$total_pages = ceil($total_passengers / $limit);

// Get passengers
$passengers = $db->query("
    SELECT p.*, 
    (SELECT COUNT(*) FROM bookings WHERE passenger_id = p.passenger_id) as booking_count
    FROM passengers p 
    $search_condition
    ORDER BY name ASC
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
            <li class="active"><a href="passengers.php"><i class="fas fa-users"></i> Passengers</a></li>
            <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Manage Passengers</h2>
            <div class="admin-user">
                <span>Welcome, <?php echo $_SESSION['staff_name']; ?></span>
                <span class="admin-role"><?php echo $_SESSION['staff_role']; ?></span>
            </div>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <form action="passengers.php" method="get" style="display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Search passengers..." value="<?php echo $search; ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <button type="submit" class="admin-btn" style="padding: 8px 15px;">Search</button>
                
                <?php if (!empty($search)): ?>
                    <a href="passengers.php" class="admin-btn" style="padding: 8px 15px; background-color: #6c757d;">Clear</a>
                <?php endif; ?>
            </form>
            
            <div>
                <span>Total Passengers: <?php echo $total_passengers; ?></span>
            </div>
        </div>
        
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Passport</th>
                        <th>Bookings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($passengers)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No passengers found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($passengers as $passenger): ?>
                            <tr>
                                <td>#<?php echo $passenger['passenger_id']; ?></td>
                                <td><?php echo $passenger['name']; ?></td>
                                <td><?php echo $passenger['email']; ?></td>
                                <td><?php echo $passenger['phone_number']; ?></td>
                                <td><?php echo $passenger['passport_number'] ? $passenger['passport_number'] : 'N/A'; ?></td>
                                <td><?php echo $passenger['booking_count']; ?></td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="view-passenger.php?id=<?php echo $passenger['passenger_id']; ?>" class="admin-btn" style="padding: 5px 10px; background-color: #17a2b8;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a href="passenger-bookings.php?id=<?php echo $passenger['passenger_id']; ?>" class="admin-btn" style="padding: 5px 10px; background-color: #28a745;">
                                            <i class="fas fa-ticket-alt"></i>
                                        </a>
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
                        <a href="passengers.php?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>" class="admin-btn" style="padding: 5px 10px;">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="passengers.php?page=<?php echo $i; ?>&search=<?php echo $search; ?>" class="admin-btn" style="padding: 5px 10px; <?php echo $i === $page ? 'background-color: #2c3e50;' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="passengers.php?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>" class="admin-btn" style="padding: 5px 10px;">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
$pageTitle = "Manage Staff";
$extraCSS = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

// Handle staff deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $staff_id = (int)$_GET['delete'];
    
    // Don't allow deleting your own account
    if ($staff_id == $_SESSION['staff_id']) {
        $delete_error = "You cannot delete your own account.";
    } else {
        // Delete staff
        if ($db->query("DELETE FROM staff WHERE staff_id = $staff_id")) {
            $delete_success = "Staff member #$staff_id has been deleted successfully.";
        } else {
            $delete_error = "Failed to delete staff member #$staff_id.";
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
        name LIKE '%$search%' OR 
        email LIKE '%$search%' OR 
        role LIKE '%$search%' OR 
        contact_number LIKE '%$search%'
    )";
}

// Get total staff count
$total_staff = $db->query("SELECT COUNT(*) as count FROM staff $search_condition")->fetch_assoc()['count'];
$total_pages = ceil($total_staff / $limit);

// Get staff
$staff = $db->query("
    SELECT * FROM staff 
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
            <li><a href="passengers.php"><i class="fas fa-users"></i> Passengers</a></li>
            <li class="active"><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
            <li><a href="aircraft.php"><i class="fas fa-plane-departure"></i> Aircraft</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h2>Manage Staff</h2>
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
            <a href="add-staff.php" class="admin-btn"><i class="fas fa-plus"></i> Add New Staff</a>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <form action="staff.php" method="get" style="display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Search staff..." value="<?php echo $search; ?>" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <button type="submit" class="admin-btn" style="padding: 8px 15px;">Search</button>
                
                <?php if (!empty($search)): ?>
                    <a href="staff.php" class="admin-btn" style="padding: 8px 15px; background-color: #6c757d;">Clear</a>
                <?php endif; ?>
            </form>
            
            <div>
                <span>Total Staff: <?php echo $total_staff; ?></span>
            </div>
        </div>
        
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staff)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No staff members found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($staff as $member): ?>
                            <tr>
                                <td>#<?php echo $member['staff_id']; ?></td>
                                <td><?php echo $member['name']; ?></td>
                                <td><?php echo $member['role']; ?></td>
                                <td><?php echo $member['email']; ?></td>
                                <td><?php echo $member['contact_number']; ?></td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="edit-staff.php?id=<?php echo $member['staff_id']; ?>" class="admin-btn" style="padding: 5px 10px; background-color: #28a745;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($member['staff_id'] != $_SESSION['staff_id']): ?>
                                            <a href="staff.php?delete=<?php echo $member['staff_id']; ?>" class="admin-btn admin-btn-danger" style="padding: 5px 10px;" onclick="return confirm('Are you sure you want to delete this staff member?');">
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
                        <a href="staff.php?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>" class="admin-btn" style="padding: 5px 10px;">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="staff.php?page=<?php echo $i; ?>&search=<?php echo $search; ?>" class="admin-btn" style="padding: 5px 10px; <?php echo $i === $page ? 'background-color: #2c3e50;' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="staff.php?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>" class="admin-btn" style="padding: 5px 10px;">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

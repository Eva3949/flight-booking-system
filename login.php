<?php
$pageTitle = "Login";
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Validate form data
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        // Check if it's an admin login attempt
        if (isset($_POST['admin_login']) && $_POST['admin_login'] == 1) {
            // Check staff credentials
            $stmt = $db->prepare("SELECT * FROM staff WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $staff = $result->fetch_assoc();
                
                if (password_verify($password, $staff['password'])) {
                    // Set session variables
                    $_SESSION['staff_id'] = $staff['staff_id'];
                    $_SESSION['staff_name'] = $staff['name'];
                    $_SESSION['staff_role'] = $staff['role'];
                    $_SESSION['is_admin'] = true;
                    
                    // Redirect to admin dashboard
                    redirect('admin/index.php');
                } else {
                    $errors[] = "Invalid password";
                }
            } else {
                $errors[] = "Staff account not found";
            }
        } else {
            // Check passenger credentials
            $stmt = $db->prepare("SELECT * FROM passengers WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $passenger = $result->fetch_assoc();
                
                if (password_verify($password, $passenger['password'])) {
                    // Set session variables
                    $_SESSION['passenger_id'] = $passenger['passenger_id'];
                    $_SESSION['passenger_name'] = $passenger['name'];
                    $_SESSION['passenger_email'] = $passenger['email'];
                    
                    // Redirect to home page
                    redirect('index.php');
                } else {
                    $errors[] = "Invalid password";
                }
            } else {
                $errors[] = "Account not found";
            }
        }
    }
}
?>

<div class="container">
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <div class="card-header">
            <h2>Login to Your Account</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="admin_login" value="1" <?php echo isset($_POST['admin_login']) ? 'checked' : ''; ?>>
                        Login as Staff/Admin
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </div>
            </form>
        </div>
        <div class="card-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

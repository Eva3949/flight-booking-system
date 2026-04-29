<?php
$pageTitle = "Register";
require_once 'includes/header.php';

$errors = [];
$success = false;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize($_POST['phone']);
    $passport = sanitize($_POST['passport']);
    $address = sanitize($_POST['address']);
    
    // Validate form data
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT * FROM passengers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Email already exists. Please use a different email or login.";
    }
    
    // If no errors, register the user
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into database
        $stmt = $db->prepare("INSERT INTO passengers (name, email, password, phone_number, passport_number, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hashed_password, $phone, $passport, $address);
        
        if ($stmt->execute()) {
            $success = true;
            
            // Set session variables
            $_SESSION['passenger_id'] = $db->getLastId();
            $_SESSION['passenger_name'] = $name;
            $_SESSION['passenger_email'] = $email;
            
            // Redirect to home page after successful registration
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<div class="container">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header">
            <h2>Create an Account</h2>
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
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Registration successful! You can now login.
                </div>
            <?php else: ?>
                <form action="register.php" method="post">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" name="name" id="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="passport">Passport Number</label>
                        <input type="text" name="passport" id="passport" value="<?php echo isset($_POST['passport']) ? $_POST['passport'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" rows="3"><?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

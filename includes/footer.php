</main>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-col">
                <h3>ASTU Flight</h3>
                <p>Your trusted partner for flight bookings and travel arrangements. We make flying simple and enjoyable.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/search.php">Search Flights</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/login.php">Login</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/register.php">Register</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Support</h3>
                <ul>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contact Info</h3>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> 123 Airport Road, Addis Ababa</li>
                    <li><i class="fas fa-phone"></i> +251 911 234 567</li>
                    <li><i class="fas fa-envelope"></i> info@astuflights.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> ASTU Flight. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
<?php if (isset($extraJS)) echo $extraJS; ?>
</body>
</html>

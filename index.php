<?php
$pageTitle = "Home";
require_once 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Explore the World with ASTU Flight</h1>
        <p>Book your flights with ease and enjoy a seamless travel experience. Discover amazing destinations and create unforgettable memories.</p>
        <a href="search.php" class="btn btn-primary">Book Now</a>
    </div>
</section>

<section class="container">
    <div class="search-form">
        <h2>Find Your Flight</h2>
        <form action="search.php" method="get">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="source">From</label>
                        <select name="source" id="source" required>
                            <option value="">Select Departure Airport</option>
                            <?php
                            $airports = getAllAirports();
                            foreach ($airports as $airport) {
                                echo "<option value=\"{$airport['airport_id']}\">{$airport['city']} ({$airport['code']}) - {$airport['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="destination">To</label>
                        <select name="destination" id="destination" required>
                            <option value="">Select Arrival Airport</option>
                            <?php
                            foreach ($airports as $airport) {
                                echo "<option value=\"{$airport['airport_id']}\">{$airport['city']} ({$airport['code']}) - {$airport['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="departure_date">Departure Date</label>
                        <input type="date" name="departure_date" id="departure_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="passengers">Passengers</label>
                        <select name="passengers" id="passengers">
                            <option value="1">1 Passenger</option>
                            <option value="2">2 Passengers</option>
                            <option value="3">3 Passengers</option>
                            <option value="4">4 Passengers</option>
                            <option value="5">5 Passengers</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Search Flights</button>
            </div>
        </form>
    </div>
</section>

<section class="features">
    <div class="container">
        <div class="features-heading">
            <h2>Why Choose Astu Flight?</h2>
            <p>We offer the best flight booking experience with a wide range of options and excellent customer service.</p>
        </div>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h3 class="feature-title">Global Coverage</h3>
                <p class="feature-desc">Access flights to hundreds of destinations worldwide with our extensive network of airline partners.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-tag"></i>
                </div>
                <h3 class="feature-title">Best Prices</h3>
                <p class="feature-desc">Find the most competitive prices and special deals for your next journey.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 class="feature-title">24/7 Support</h3>
                <p class="feature-desc">Our customer service team is available round the clock to assist you with any queries.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">Secure Booking</h3>
                <p class="feature-desc">Your personal and payment information is protected with advanced security measures.</p>
            </div>
        </div>
    </div>
</section>

<?php
// Get upcoming flights for display
$upcomingFlights = getAllFlights();
if (count($upcomingFlights) > 0) {
?>
<section class="container">
    <h2 class="section-title">Upcoming Flights</h2>
    <div class="card">
        <?php foreach (array_slice($upcomingFlights, 0, 5) as $flight) { ?>
            <div class="flight-card">
                <div class="flight-logo">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="flight-details">
                    <div class="flight-route">
                        <div class="flight-city">
                            <div><?php echo $flight['source_city']; ?> (<?php echo $flight['source_code']; ?>)</div>
                            <div class="flight-time"><?php echo formatTime($flight['departure_time']); ?></div>
                        </div>
                        <div class="flight-route-divider"></div>
                        <div class="flight-city">
                            <div><?php echo $flight['destination_city']; ?> (<?php echo $flight['destination_code']; ?>)</div>
                            <div class="flight-time"><?php echo formatTime($flight['arrival_time']); ?></div>
                        </div>
                    </div>
                    <div class="flight-info">
                        <div><i class="fas fa-calendar"></i> <?php echo formatDate($flight['departure_time']); ?></div>
                        <div><i class="fas fa-clock"></i> <?php echo formatDuration($flight['duration']); ?></div>
                        <div><i class="fas fa-plane"></i> <?php echo $flight['airline_name']; ?></div>
                        <div><i class="fas fa-tag"></i> <?php echo $flight['flight_type']; ?></div>
                    </div>
                </div>
                <div class="flight-price">
                    <div class="price">Birr <?php echo number_format($flight['base_price'], 2); ?></div>
                    <a href="booking.php?flight_id=<?php echo $flight['flight_id']; ?>" class="btn btn-primary">Book Now</a>
                </div>
            </div>
        <?php } ?>
    </div>
</section>
<?php } ?>

<?php
// Footer
echo '</main>';
?>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-col">
                <h3>ASTU</h3>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="search.php">Search Flights</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
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
                    <li><i class="fas fa-envelope"></i> info@astu.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Astu Flight Booking. All rights reserved. | Developed by ASTU STUDENTS</p>
        </div>
    </div>
</footer>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
<?php if (isset($extraJS)) echo $extraJS; ?>
</body>
</html>

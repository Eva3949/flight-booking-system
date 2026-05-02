<?php
// Auto-detect the current domain and update config
$current_domain = $_SERVER['HTTP_HOST'];
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$full_url = $protocol . '://' . $current_domain;

echo "<h3>Domain Detection</h3>";
echo "Current Domain: " . $current_domain . "<br>";
echo "Full URL: " . $full_url . "<br>";
echo "Current SITE_URL: " . SITE_URL . "<br>";

echo "<br><h3>Recommended SITE_URL:</h3>";
echo "<code>define('SITE_URL', '" . $full_url . "');</code>";

echo "<br><br><h3>Test CSS with current domain:</h3>";
$css_url = $full_url . "/assets/css/style.css";
echo "CSS URL: " . $css_url . "<br>";

$css_content = @file_get_contents($css_url);
if ($css_content) {
    echo "✓ CSS accessible via current domain: YES<br>";
    echo "✓ CSS content length: " . strlen($css_content) . " characters<br>";
} else {
    echo "✗ CSS accessible via current domain: NO<br>";
}

echo "<br><h3>Instructions:</h3>";
echo "1. Copy the recommended SITE_URL above<br>";
echo "2. Update your config.php file with the correct SITE_URL<br>";
echo "3. Re-upload the config.php file<br>";
echo "4. Clear your browser cache and refresh<br>";
?>

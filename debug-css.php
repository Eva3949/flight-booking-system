<?php
// Debug script to check CSS file
echo "<h3>CSS File Debug</h3>";

$css_path = "assets/css/style.css";
$full_path = $_SERVER['DOCUMENT_ROOT'] . "/" . $css_path;

echo "CSS Path: " . $css_path . "<br>";
echo "Full Path: " . $full_path . "<br>";
echo "File exists: " . (file_exists($full_path) ? "YES" : "NO") . "<br>";

if (file_exists($full_path)) {
    echo "File size: " . filesize($full_path) . " bytes<br>";
    echo "Readable: " . (is_readable($full_path) ? "YES" : "NO") . "<br>";
}

echo "<br>";
echo "Current SITE_URL: " . SITE_URL . "<br>";
echo "CSS URL: " . SITE_URL . "/assets/css/style.css<br>";

// Test if CSS is accessible via URL
$css_url = SITE_URL . "/assets/css/style.css";
$css_content = @file_get_contents($css_url);
if ($css_content) {
    echo "CSS accessible via URL: YES<br>";
    echo "CSS content length: " . strlen($css_content) . " characters<br>";
} else {
    echo "CSS accessible via URL: NO<br>";
}
?>

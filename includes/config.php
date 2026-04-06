<?php
// Database Configuration
define('DB_HOST', 'sql12.freesqldatabase.com');
define('DB_USER', 'sql12822389');
define('DB_PASS', 'YSGWqT2CFM');
define('DB_NAME', 'sql12822389');
define('SITE_NAME', 'FootballHub');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("
    <div style='font-family:sans-serif;padding:40px;text-align:center;background:#0a0a0a;color:#fff;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;'>
        <h1 style='color:#e74c3c;font-size:3rem;margin-bottom:16px'>⚠ Database Error</h1>
        <p style='color:#aaa;font-size:1.1rem;margin-bottom:24px'>Could not connect to the database. Please check your configuration.</p>
        <div style='background:#1a1a1a;padding:20px 32px;border-radius:8px;border:1px solid #333;text-align:left;'>
            <p style='color:#e74c3c;font-weight:bold;margin:0 0 8px'>Steps to fix:</p>
            <ol style='color:#ccc;line-height:2'>
                <li>Open <strong>phpMyAdmin</strong> via XAMPP</li>
                <li>Import <strong>database.sql</strong> file</li>
                <li>Update credentials in <strong>includes/config.php</strong></li>
            </ol>
        </div>
    </div>");
}

$conn->set_charset("utf8");

// Helper: Get points from wins/draws
function getPoints($wins, $draws) {
    return ($wins * 3) + ($draws * 1);
}

// Helper: Goal difference
function getGD($scored, $conceded) {
    return $scored - $conceded;
}

// Helper: Matches played
function getMatchesPlayed($wins, $losses, $draws) {
    return $wins + $losses + $draws;
}

// Flash message helper
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
?>
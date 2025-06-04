<?php
// config.php
// ---------------------
// Παραμετροποίηση και σύνδεση με mySQL

// Έναρξη Session
session_start();

// Ορισμός παραμέτρων σύνδεση με mySQL
define('DB_HOST', '192.168.88.100');
define('DB_PORT', '3306');
define('DB_NAME', 'recipe_network');
define('DB_USER', 'orasimos');
define('DB_PASS', 'Orasimos123!');

// Δημιουργία σύνδεσης με mySQL με mysqli
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Διαχείριση αποτυχίας σύνδεσης με τη βάση
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}
// Ορισμός charset για υποστήριξη emojis (κυρίως στα σχόλια των χρηστών)
$mysqli->set_charset("utf8mb4");

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// Ανακατεύθυσνη στη σελίδα login
function ensure_logged_in()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Επιστροφή τρέχοντος χρήστη
function current_user()
{
    global $mysqli;
    if (!is_logged_in())
        return null;
    $stmt = $mysqli->prepare("
        SELECT id, username, email, display_name, avatar_path, created_at 
        FROM users 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}
?>
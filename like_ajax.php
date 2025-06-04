<?php
// like_ajax.php
require 'config.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$user = current_user();
$recipe_id = intval($_POST['recipe_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$recipe_id || !in_array($action, ['like','unlike'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

// Check recipe exists
$stmt = $mysqli->prepare("SELECT id FROM recipes WHERE id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Recipe not found.']);
    $stmt->close();
    exit;
}
$stmt->close();

if ($action === 'like') {
    // Insert if not already liked
    $stmt2 = $mysqli->prepare("INSERT IGNORE INTO likes (user_id, recipe_id) VALUES (?, ?)");
    $stmt2->bind_param("ii", $user['id'], $recipe_id);
    $stmt2->execute();
    $stmt2->close();
} else {
    // Delete if exists
    $stmt2 = $mysqli->prepare("DELETE FROM likes WHERE user_id = ? AND recipe_id = ?");
    $stmt2->bind_param("ii", $user['id'], $recipe_id);
    $stmt2->execute();
    $stmt2->close();
}

// Get updated like count
$stmt3 = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM likes WHERE recipe_id = ?");
$stmt3->bind_param("i", $recipe_id);
$stmt3->execute();
$stmt3->bind_result($new_count);
$stmt3->fetch();
$stmt3->close();

echo json_encode(['success' => true, 'like_count' => $new_count]);
exit;
?>

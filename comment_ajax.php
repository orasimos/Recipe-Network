<?php
// comment_ajax.php
require 'config.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$user = current_user();
$recipe_id = intval($_POST['recipe_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if (!$recipe_id || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Recipe and comment content required.']);
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

// Insert comment
$stmt2 = $mysqli->prepare("INSERT INTO comments (user_id, recipe_id, content) VALUES (?, ?, ?)");
$stmt2->bind_param("iis", $user['id'], $recipe_id, $content);
if (!$stmt2->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to save comment.']);
    $stmt2->close();
    exit;
}
$comment_id = $stmt2->insert_id;
$stmt2->close();

// Fetch inserted comment with timestamp
$stmt3 = $mysqli->prepare("
    SELECT c.id, c.content, c.created_at, u.username, u.display_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
");
$stmt3->bind_param("i", $comment_id);
$stmt3->execute();
$res3 = $stmt3->get_result();
$comment = $res3->fetch_assoc();
$stmt3->close();

// Build HTML for the new comment to return
ob_start();
?>
<div class="comment" id="comment-<?= $comment['id'] ?>">
    <p>
        <strong><?= htmlspecialchars($comment['display_name'] ?: $comment['username']) ?></strong>
        <small><?= date('Y-m-d H:i') ?></small>
    </p>
    <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
</div>
<?php
$html = ob_get_clean();

echo json_encode(['success' => true, 'comment_html' => $html]);
exit;
?>

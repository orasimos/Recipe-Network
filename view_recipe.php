<?php
// view_recipe.php
require 'config.php';
$user = current_user();

$recipe_id = intval($_GET['id'] ?? 0);
if (!$recipe_id) {
    header('Location: index.php');
    exit;
}

// Fetch recipe + uploader
$stmt = $mysqli->prepare("
    SELECT r.id, r.title, r.description, r.image_path, r.created_at,
           u.username, u.display_name, u.avatar_path
    FROM recipes r
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ?
");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$res = $stmt->get_result();
$recipe = $res->fetch_assoc();
$stmt->close();

if (!$recipe) {
    echo "Recipe not found.";
    exit;
}

// Count likes
$stmt2 = $mysqli->prepare("SELECT COUNT(*) AS cnt FROM likes WHERE recipe_id = ?");
$stmt2->bind_param("i", $recipe_id);
$stmt2->execute();
$stmt2->bind_result($like_count);
$stmt2->fetch();
$stmt2->close();

// Check if current user already liked
$user_liked = false;
if ($user) {
    $stmt3 = $mysqli->prepare("SELECT 1 FROM likes WHERE recipe_id = ? AND user_id = ?");
    $stmt3->bind_param("ii", $recipe_id, $user['id']);
    $stmt3->execute();
    $stmt3->store_result();
    $user_liked = ($stmt3->num_rows > 0);
    $stmt3->close();
}

// Fetch comments with commenter info
$stmt4 = $mysqli->prepare("
    SELECT c.id, c.content, c.created_at, u.username, u.display_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.recipe_id = ?
    ORDER BY c.created_at ASC
");
$stmt4->bind_param("i", $recipe_id);
$stmt4->execute();
$res4 = $stmt4->get_result();
$comments = [];
while ($row = $res4->fetch_assoc()) {
    $comments[] = $row;
}
$stmt4->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($recipe['title']) ?> – Recipe Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" href="assets/logo.png">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">Recipe Network</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <?php if ($user): ?>
                        <li class="nav-item">
                            <span class="nav-link">Hello, <?= htmlspecialchars($user['username']) ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">My Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5" style="max-width: 800px;">
        <a href="index.php" class="btn btn-link mb-3">&laquo; Back to Feed</a>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h2>
                <p class="text-muted">
                    By <?= htmlspecialchars($recipe['display_name'] ?: $recipe['username']) ?> on
                    <?= date('Y-m-d', strtotime($recipe['created_at'])) ?>
                </p>
                <?php if (!empty($recipe['image_path'])): ?>
                    <img src="<?= htmlspecialchars($recipe['image_path']) ?>" class="img-fluid rounded mb-3"
                        alt="<?= htmlspecialchars($recipe['title']) ?>" />
                <?php endif; ?>
                <div class="mb-3">
                    <p><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
                </div>

                <div class="d-flex align-items-center mb-3">
                    <span class="me-2">👍</span>
                    <span id="likeCount"><?= $like_count ?></span>
                    <?php if ($user): ?>
                        <?php if ($user_liked): ?>
                            <button id="btnToggleLike" class="btn btn-sm btn-danger ms-3" data-id="<?= $recipe_id ?>"
                                data-action="unlike">
                                Unlike
                            </button>
                        <?php else: ?>
                            <button id="btnToggleLike" class="btn btn-sm btn-success ms-3" data-id="<?= $recipe_id ?>"
                                data-action="like">
                                Like
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="ms-3 text-muted"><em>Log in to like</em></span>
                    <?php endif; ?>
                </div>

                <hr />

                <h4>Comments (<?= count($comments) ?>)</h4>
                <?php if ($comments): ?>
                    <div id="commentsList" class="mb-4">
                        <?php foreach ($comments as $c): ?>
                            <div class="border-bottom pb-2 mb-2" id="comment-<?= $c['id'] ?>">
                                <p class="mb-1">
                                    <strong><?= htmlspecialchars($c['display_name'] ?: $c['username']) ?></strong>
                                    <small class="text-muted">
                                        <?= date('Y-m-d H:i', strtotime($c['created_at'])) ?>
                                    </small>
                                </p>
                                <p><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p id="noComments"><em>No comments yet.</em></p>
                    <div id="commentsList"></div>
                <?php endif; ?>

                <?php if ($user): ?>
                    <form id="commentForm" method="POST" action="comment_ajax.php">
                        <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>" />
                        <div class="mb-3">
                            <textarea class="form-control" id="commentContent" name="content" rows="3"
                                placeholder="Add a comment..." required></textarea>
                        </div>
                        <button id="commentSubmitBtn" type="submit" class="btn btn-primary">Post Comment</button>
                    </form>
                <?php else: ?>
                    <p><em>Log in to post a comment.</em></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle (Popper + JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (for our scripts.js) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
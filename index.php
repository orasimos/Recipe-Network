<?php
// index.php
require 'config.php';
$user = current_user();

// Fetch all recipes (latest first), with joined info for like_count and comment_count
$query = "
    SELECT
        r.id, r.title, r.image_path, r.created_at,
        u.username, u.display_name,
        (SELECT COUNT(*) FROM likes WHERE recipe_id = r.id) AS like_count,
        (SELECT COUNT(*) FROM comments WHERE recipe_id = r.id) AS comment_count
    FROM recipes r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
";
$result = $mysqli->query($query);
$recipes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }
    $result->free();
}

// If logged in, fetch this user’s liked recipe IDs into an array
$user_likes = [];
if ($user) {
    $stmt = $mysqli->prepare("SELECT recipe_id FROM likes WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $user_likes[] = $r['recipe_id'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>All Recipes – Recipe Network</title>
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

    <div class="container mt-5">
        <h2 class="mb-4">Recipe Feed</h2>

        <?php if (empty($recipes)): ?>
            <div class="alert alert-info">No recipes have been uploaded yet.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($recipes as $r): ?>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <?php if (!empty($r['image_path'])): ?>
                                <img src="<?= htmlspecialchars($r['image_path']) ?>" class="card-img-top"
                                    alt="<?= htmlspecialchars($r['title']) ?>" style="height: 180px; object-fit: cover;" />
                            <?php else: ?>
                                <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center"
                                    style="height: 180px;">
                                    No Image
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column recipe-card">
                                <h5 class="card-title"><?= htmlspecialchars($r['title']) ?></h5>
                                <p class="card-text mb-1">
                                    <small class="text-muted">
                                        By <?= htmlspecialchars($r['display_name'] ?: $r['username']) ?>
                                        on <?= date('Y-m-d', strtotime($r['created_at'])) ?>
                                    </small>
                                </p>
                                <p class="mb-3">
                                    👍 <span class="like-count"><?= $r['like_count'] ?></span>
                                    &nbsp;
                                    💬 <?= $r['comment_count'] ?>
                                </p>
                                <div class="mt-auto">
                                    <a href="view_recipe.php?id=<?= $r['id'] ?>" class="btn btn-outline-primary btn-sm">View
                                        Recipe</a>
                                    <?php if ($user): ?>
                                        <?php if (in_array($r['id'], $user_likes)): ?>
                                            <button class="btn btn-danger btn-sm ms-2 btn-unlike" data-id="<?= $r['id'] ?>"
                                                data-action="unlike">
                                                Unlike
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-success btn-sm ms-2 btn-like" data-id="<?= $r['id'] ?>"
                                                data-action="like">
                                                Like
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS Bundle (Popper + JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (for our scripts.js) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
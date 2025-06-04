<?php
// profile.php
require 'config.php';
ensure_logged_in();

// Fetch current user
$user = current_user();

// Initialize message arrays
$errors = [];
$success = '';

// Handle avatar upload if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    // Check for upload errors
    if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file. Please try again.";
    } else {
        // Validate MIME type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['avatar']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Avatar must be a JPEG, PNG, or GIF image.";
        } else {
            // Generate a unique filename (preserve extension)
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $newName = uniqid('avatar_') . "." . $ext;

            // Ensure uploads/avatars directory exists
            $uploadDir = 'uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destination = $uploadDir . $newName;
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $errors[] = "Failed to move uploaded file.";
            } else {
                // Update user's avatar_path in the database
                $stmt = $mysqli->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
                $stmt->bind_param("si", $destination, $user['id']);
                if ($stmt->execute()) {
                    $stmt->close();
                    // Redirect to self so refresh won’t re‐POST
                    header("Location: profile.php");
                    exit;
                } else {
                    $errors[] = "Database update failed. Please try again.";
                    $stmt->close();
                    // Optionally unlink the file, since DB didn’t update:
                    if (file_exists($destination)) {
                        unlink($destination);
                    }
                }
            }
        }
    }
}

// Re‐fetch user (in case avatar_path changed)
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($user['display_name'] ?? $user['username']) ?>’s Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" href="assets/logo.png">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">Recipe Network</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Hello, <?= htmlspecialchars($user['username']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <!-- Profile Info & Avatar Upload -->
            <div class="col-md-4 mb-4">
                <!-- Show errors/success -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Display current avatar (or placeholder) -->
                <div class="card text-center">
                    <?php if (!empty($user['avatar_path']) && file_exists($user['avatar_path'])): ?>
                        <img src="<?= htmlspecialchars($user['avatar_path']) ?>"
                            class="card-img-top rounded-circle mx-auto mt-3" alt="Avatar"
                            style="width: 120px; height: 120px; object-fit: cover;" />
                    <?php else: ?>
                        <div class="card-img-top bg-secondary text-white rounded-circle mx-auto mt-3"
                            style="width: 120px; height: 120px; line-height: 120px; text-align: center;">
                            No Avatar
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($user['display_name'] ?: $user['username']) ?></h5>
                        <p class="card-text">
                            <strong>Email:</strong><br />
                            <?= htmlspecialchars($user['email']) ?>
                        </p>
                        <!-- Avatar upload form -->
                        </br>
                        <form method="POST" action="profile.php" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Change Avatar</label>
                                <input class="form-control" type="file" id="avatar" name="avatar" accept="image/*"
                                    required />
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Upload</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- User’s Recipes List -->
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Your Recipes</h3>
                    <a href="upload_recipe.php" class="btn btn-success">Upload New Recipe</a>
                </div>

                <?php
                // Fetch user’s recipes
                $stmt = $mysqli->prepare("
                    SELECT id, title, created_at
                    FROM recipes
                    WHERE user_id = ?
                    ORDER BY created_at DESC
                ");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user_recipes = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                ?>

                <?php if (empty($user_recipes)): ?>
                    <div class="alert alert-info">You haven’t uploaded any recipes yet.</div>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($user_recipes as $r): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="view_recipe.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></a>
                                <span class="badge bg-secondary">
                                    <?= date('Y-m-d', strtotime($r['created_at'])) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle (Popper + JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
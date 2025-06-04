<?php
// upload_recipe.php
require 'config.php';
ensure_logged_in();

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($description)) {
        $errors[] = "Description is required.";
    }

    // Handle image upload (optional)
    $image_path = null;
    if (!empty($_FILES['recipe_image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['recipe_image']['type'], $allowed_types)) {
            $errors[] = "Image must be JPEG, PNG, or GIF.";
        }
        if ($_FILES['recipe_image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading image.";
        }

        if (empty($errors)) {
            $ext = pathinfo($_FILES['recipe_image']['name'], PATHINFO_EXTENSION);
            $new_name = uniqid('recipe_', true) . '.' . $ext;
            $upload_dir = 'uploads/recipes/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $dest = $upload_dir . $new_name;
            if (move_uploaded_file($_FILES['recipe_image']['tmp_name'], $dest)) {
                $image_path = $dest;
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO recipes (user_id, title, description, image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user['id'], $title, $description, $image_path);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: profile.php');
            exit;
        } else {
            $errors[] = "Failed to save recipe. Try again.";
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Upload Recipe – Recipe Network</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">My Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5" style="max-width: 700px;">
        <h2 class="mb-4">Upload a New Recipe</h2>
        <p>
            <a href="profile.php" class="btn btn-link">&laquo; Back to Profile</a>
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="recipeForm" method="POST" action="upload_recipe.php" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
                <label for="title" class="form-label">Recipe Title</label>
                <input type="text" class="form-control" id="title" name="title" required />
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description / Instructions</label>
                <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
            </div>

            <div class="mb-3">
                <label for="recipe_image" class="form-label">Upload Image (optional)</label>
                <input class="form-control" type="file" id="recipe_image" name="recipe_image" accept="image/*" />
            </div>

            <button type="submit" class="btn btn-success">Submit Recipe</button>
        </form>
    </div>

    <!-- Bootstrap 5 JS Bundle (Popper + JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (for our scripts.js) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
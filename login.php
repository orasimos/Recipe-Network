<?php
// login.php

// φόρτωση αρχείου config.php για πρόσβαση στη βάση και στο session
require 'config.php';

// Πίνακας που θα γεμίσει με τα σφάλματα από 
// την αξιολόγηση των δεδομένων εισαγωγής
$errors = [];

// Το submit της φόρμας είναι κλήση τύπου 'POST'
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Εκχώριση δεδομένων φόρμας σε μεταβλητές για ευκολότερη επεξεργασία.
    $username_or_email = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side έλεγχος δεδομένων
    if (empty($username_or_email) || empty($password)) {
        $errors[] = "Please fill in both fields.";
    } else {
        // Αναζήτηση χρήστη στη βάση
        $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username_or_email, $username_or_email);
        $stmt->execute();
        $stmt->bind_result($id, $password_hash);
        // Αν ο χρήστης βρεθεί
        if ($stmt->fetch()) {
            // Σύγκριση password hashes
            if (password_verify($password, $password_hash)) {
                // Ορισμός user_id στο session
                $_SESSION['user_id'] = $id;
                $stmt->close();
                header('Location: profile.php');
                exit;
            } else {
                // Διαχείριση λάθος κωδικού
                $errors[] = "Invalid credentials.";
            }
        } else {
            // Διαχείριση λάθος username / email
            $errors[] = "Invalid credentials.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Login – Recipe Network</title>
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
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5" style="max-width: 500px;">
        <h2 class="mb-4 text-center">Login</h2>
        
        <!-- Εμφάνιση μηνυμάτων με τα σφάλματα που προέκυψαν κατά τον έλεγχο της φόρμας -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Φόρμα login -->
        <form id="loginForm" method="POST" action="login.php" novalidate>
            <!-- Username ή Email -->
            <div class="form-floating mb-3">                
                <input type="text" class="form-control" id="username_or_email" name="username_or_email" placeholder="Username or Email" required />
                <label for="username_or_email" class="form-label">Username or Email</label>
            </div>

            <!-- Password -->
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required />
                <label for="password" class="form-label">Password</label>
            </div>

            <!-- Κουμπί Καταχώρισης -->
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <!-- Σύνδεσμος ανακατεύθυνσης στη σελίδα register.php -->
        <div class="text-center mt-3">
            Don't have an account? <a href="register.php">Register here</a>.
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle (Popper + JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (for our scripts.js) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
<?php
// register.php

// φόρτωση αρχείου config.php για πρόσβαση στη βάση και στο session
require 'config.php';

// Πίνακας που θα γεμίσει με τα σφάλματα από 
// την αξιολόγηση των δεδομένων εισαγωγής
$errors = [];

// Το submit της φόρμας είναι κλήση τύπου 'POST'
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Εκχώριση δεδομένων φόρμας σε μεταβλητές για ευκολότερη επεξεργασία.
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Server-side έλεγχος δεδομένων
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }
    // Το email πρέπει να είναι της μοφρής 'user@company.com'
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    // Το μήκος του password πρέπει να είναι τουλάχιστον 6 χαρακτήρες
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    // Το password και το confirm_password πρέπει να είναι ίδια
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Έλεγχος μοναδικότητας χρήστη αν δεν προέκυψαν σφάλματα παραπάνω
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username or email already taken.";
        }
        $stmt->close();
    }

    // Καταχώριση στη βάση αν δεν προέκυψαν σφάλματα παραπάνω
    if (empty($errors)) {
        // δημιουργία hash από το password για κρυπτογράφιση
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password_hash);
        if ($stmt->execute()) {
            //Αν η καταχώριση πραγματοποιηθεί επιτυχώς ορίζεται στο τρέχον session το user_id.
            //Έτσι ο χρήστης θεωρείται logged in.
            $_SESSION['user_id'] = $stmt->insert_id;
            $stmt->close();
            header('Location: profile.php');
            exit;
        } else {
            //Διαχείριση σφάλματος εγγραφής λόγω internal server error
            $errors[] = "Registration failed. Please try again.";
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />    
    <title>Register - Recipe Network</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Favicon -->
    <link rel="icon" href="assets/logo.png">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">Recipe Network</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5" style="max-width: 500px;">
        <h2 class="mb-4 text-center">Create an Account</h2>

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

        <!-- Φόρμα εγγραφής -->
        <form id="registerForm" method="POST" action="register.php" novalidate>
            <!-- Username -->
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required
                    minlength="3" />
                <label for="username" class="form-label">Username</label>
            </div>

            <!-- Email -->
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required />
                <label for="email" class="form-label">Email address</label>
            </div>

            <!-- Password -->
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                    required minlength="6" />
                <label for="password" class="form-label">Password</label>
            </div>

            <!-- Επιβεβαίωση Password -->
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                    placeholder="Confirm Password" required minlength="6" />
                <label for="confirm_password" class="form-label">Confirm Password</label>
            </div>

            <!-- Κουμπί Καταχώρισης -->
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <!-- Σύνδεσμος ανακατεύθυνσης στη σελίδα login -->
        <div class="text-center mt-3">
            Already have an account? <a href="login.php">Login here</a>.
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery και scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
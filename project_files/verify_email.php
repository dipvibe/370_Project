<?php
session_start();
include "connection.php";

$error = "";
$success = "";

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    // Validate token format (should be 64 hex characters)
    if (strlen($token) !== 64 || !ctype_xdigit($token)) {
        $error = "Invalid verification link.";
    } else {
        // Find user with this token
        $stmt = $conn->prepare("SELECT user_id, name, is_verified FROM General_User WHERE verification_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['is_verified'] == 1) {
                $success = "Your email is already verified. You can login now.";
            } else {
                // Update user as verified and clear the token
                $update = $conn->prepare("UPDATE General_User SET is_verified = 1, verification_token = NULL WHERE user_id = ?");
                $update->bind_param("i", $user['user_id']);
                
                if ($update->execute()) {
                    // Redirect to login with success message
                    header("Location: login.php?verified=1");
                    exit;
                } else {
                    $error = "Verification failed. Please try again.";
                }
            }
        } else {
            $error = "Invalid or expired verification link.";
        }
    }
} else {
    $error = "No verification token provided.";
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Email Verification</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="auth-hero">
  <div id="form">
    <h1>Email Verification</h1>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
      </div>
      <div class="text-center mt-3">
        <a href="login.php" class="btn btn-primary">Go to Login</a>
      </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="bi bi-x-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
      <div class="text-center mt-3">
        <p>Need a new verification link?</p>
        <a href="resend_verification.php" class="btn btn-outline-primary">Resend Verification Email</a>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- Footer -->
<footer class="footer mt-auto py-4 bg-dark text-white">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Household Service Network</h5>
                <p class="text-muted">Connecting households with trusted service professionals.</p>
            </div>
            <div class="col-md-3">
                <h6>Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-muted">Home</a></li>
                    <li><a href="login.php" class="text-muted">Login</a></li>
                    <li><a href="signup.php" class="text-muted">Sign Up</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6>Contact</h6>
                <p class="text-muted mb-1">support@householdnetwork.com</p>
                <p class="text-muted">+880 1234-567890</p>
            </div>
        </div>
        <hr class="my-3 bg-secondary">
        <div class="text-center text-muted">
            <small>&copy; <?= date('Y') ?> Household Service Network. All rights reserved.</small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

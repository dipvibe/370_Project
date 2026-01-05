<?php
session_start();
include "connection.php";
include "mail_config.php"; // PHPMailer configuration

$error = "";
$success = "";

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    
    // Find user by email
    $stmt = $conn->prepare("SELECT user_id, name, is_verified, verification_token FROM General_User WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['is_verified'] == 1) {
            $error = "This email is already verified. You can login directly.";
        } else {
            // Generate new token if none exists
            $token = $user['verification_token'];
            if (empty($token)) {
                $token = bin2hex(random_bytes(32));
                $update = $conn->prepare("UPDATE General_User SET verification_token = ? WHERE user_id = ?");
                $update->bind_param("si", $token, $user['user_id']);
                $update->execute();
            }
            
            // Send verification email
            if (sendVerificationEmail($email, $user['name'], $token)) {
                $success = "Verification email sent! Please check your inbox.";
            } else {
                // Email failed - show token for testing
                $success = "Email could not be sent. <br><small>For testing, use this link: <a href='verify_email.php?token=$token'>Verify Email</a></small>";
            }
        }
    } else {
        $error = "No account found with this email address.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Resend Verification</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="auth-hero">
  <div id="form">
    <h1>Resend Verification</h1>
    <p class="text-muted mb-4">Enter your email address to receive a new verification link.</p>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Email Address</label>
      <input type="email" name="email" required>

      <input type="submit" id="btn" name="submit" value="Resend Verification Email">
    </form>

    <p class="text-center mt-3">
      Remember your password? <a href="login.php">Login</a>
    </p>
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

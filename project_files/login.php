<?php
session_start();
include "connection.php";

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: welcome.php");
    exit;
}

$error = "";

if (isset($_POST['submit'])) {
    $login    = trim($_POST['login']);
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT user_id, name, password, role
         FROM General_User
         WHERE email = ? OR name = ?"
    );
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['role']     = $user['role'];
            header("Location: welcome.php");
            exit;
        }
    }
    $error = "Invalid name/email or password.";
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="auth-hero">
  <div id="form">
    <h1>Login</h1>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Name / Email</label>
      <input type="text" name="login" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <input type="submit" id="btn" name="submit" value="Login">
    </form>

    <p class="text-center mt-3">
      Don’t have an account? <a href="signup.php">Signup</a>
    </p>
  </div>
</div>

</body>
</html>


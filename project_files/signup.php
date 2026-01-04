<?php
session_start();
include "connection.php";

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: welcome.php");
    exit;
}

$error = "";

if (isset($_POST['submit'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $address  = trim($_POST['address']);
    $password = $_POST['password'];
    $cpass    = $_POST['cpassword'];
    $role     = $_POST['role'];

    if ($password !== $cpass) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT user_id FROM General_User WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO General_User (name, email, address, password, role)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $name, $email, $address, $hash, $role);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;

                if ($role === 'worker') {
                    $conn->query("INSERT INTO Worker (user_id, experience, availability) VALUES ($user_id, 0, 'available')");
                } elseif ($role === 'employer') {
                    $conn->query("INSERT INTO Employer (user_id) VALUES ($user_id)");
                } elseif ($role === 'administrator') {
                    $conn->query("INSERT INTO Administrator (user_id) VALUES ($user_id)");
                }

                header("Location: login.php");
                exit;
            } else {
                $error = "Signup failed.";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Signup</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="auth-hero">
  <div id="form">
    <h1>Signup</h1>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Name</label>
      <input type="text" name="name" required>

      <label>Email</label>
      <input type="email" name="email" required>

      <label>Address</label>
      <input type="text" name="address">

      <label>Password</label>
      <input type="password" name="password" required>

      <label>Confirm Password</label>
      <input type="password" name="cpassword" required>

      <label>Role</label>
      <select name="role" class="form-select" required>
        <option value="">Select Role</option>
        <option value="employer">Employer</option>
        <option value="worker">Worker</option>
        <option value="administrator">Administrator</option>
      </select>

      <input type="submit" id="btn" name="submit" value="Sign Up">
    </form>

    <p class="text-center mt-3">
      Already have an account? <a href="login.php">Login</a>
    </p>
  </div>
</div>

</body>
</html>

<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'employer') {
    header("Location: welcome.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

/* ===============================
   UPDATE PROFILE
================================ */
if (isset($_POST['update_profile'])) {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    /* Update General_User (name, email) */
    $stmt = $conn->prepare(
        "UPDATE General_User
         SET name = ?, email = ?
         WHERE user_id = ?"
    );
    $stmt->bind_param("ssi", $name, $email, $user_id);
    $stmt->execute();
    $stmt->close();

    /* Update Employer (phone) */
    $stmt = $conn->prepare(
        "UPDATE Employer
         SET phone = ?
         WHERE user_id = ?"
    );
    $stmt->bind_param("si", $phone, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['username'] = $name;
    $message = "Profile updated successfully.";
}

/* ===============================
   FETCH PROFILE DATA
================================ */
$stmt = $conn->prepare(
    "SELECT 
        g.name,
        g.email,
        e.phone
     FROM General_User g
     JOIN Employer e ON g.user_id = e.user_id
     WHERE g.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone);
$stmt->fetch();
$stmt->close();

/* ===============================
   FETCH REVIEWS
================================ */
$stmt = $conn->prepare(
    "SELECT 
        r.rating,
        r.comment,
        g.name AS reviewer_name,
        r.created_at
     FROM Review r
     JOIN General_User g 
        ON r.reviewer_id = g.user_id
     WHERE r.reviewed_id = ?
     ORDER BY r.created_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reviews = $stmt->get_result();
$stmt->close();

/* Average rating */
$stmt = $conn->prepare(
    "SELECT ROUND(AVG(rating), 1)
     FROM Review
     WHERE reviewed_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($avg_rating);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .profile-card {
          background: white;
          padding: 30px;
          border-radius: 10px;
          box-shadow: 0 0 15px rgba(0,0,0,0.2);
      }
      .dashboard-hero {
          align-items: flex-start;
          padding-top: 60px;
          padding-bottom: 60px;
          min-height: 100vh;
      }
  </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="dashboard-hero">
    <div class="container">
        <div class="profile-card mx-auto" style="max-width: 900px;">
            <h2 class="text-center mb-4" style="color: #6c16be;">My Profile</h2>

            <?php if ($message): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Update Profile Section -->
                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light h-100">
                        <h4 class="mb-3">Update Profile</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="Enter new phone number" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary w-100" style="background-color: #6c16be; border: none;">Update Profile</button>
                        </form>
                    </div>
                </div>

                <!-- Saved Profile & Stats -->
                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light h-100">
                        <h4 class="mb-3">Saved Profile</h4>
                        <ul class="list-group mb-4">
                            <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($name) ?></li>
                            <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($email) ?></li>
                            <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($phone ?? '-') ?></li>
                        </ul>

                        <h4 class="mb-3">Reviews</h4>
                        <?php if ($avg_rating): ?>
                            <div class="alert alert-info">
                                <strong>Average Rating:</strong> ⭐ <?= $avg_rating ?> / 5
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No reviews yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <?php if ($reviews->num_rows > 0): ?>
                <hr class="my-5">
                <h3 class="mb-3">Recent Reviews</h3>
                <div class="list-group">
                <?php while ($row = $reviews->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?= htmlspecialchars($row['reviewer_name']) ?></h5>
                            <small class="text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                        </div>
                        <p class="mb-1 text-warning">
                            <?php for($i=0; $i<$row['rating']; $i++) echo '⭐'; ?>
                        </p>
                        <p class="mb-1"><?= htmlspecialchars($row['comment']) ?></p>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer">
  <p><strong>Contact Information</strong></p>
  <p>📞 +880 1234 567890</p>
  <p>📧 support@householdnetwork.com</p>
  <p>📍 Dhaka, Bangladesh</p>
  <p class="copyright">
    © 2026 House Hold Network. All rights reserved.
  </p>
</footer>

</body>
</html>

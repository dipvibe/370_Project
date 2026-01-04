<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'worker') {
    header("Location: welcome.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

/* ===============================
   UPDATE PROFILE
================================ */
if (isset($_POST['update_profile'])) {

    /* General user info */
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);

    /* Worker info */
    $phone        = trim($_POST['phone']);
    $experience   = (int)$_POST['experience'];
    $availability = trim($_POST['availability']);
    $skills_input = trim($_POST['skills']);

    /* Update General_User */
    $stmt = $conn->prepare(
        "UPDATE General_User
         SET name = ?, email = ?
         WHERE user_id = ?"
    );
    $stmt->bind_param("ssi", $name, $email, $user_id);
    $stmt->execute();
    $stmt->close();

    /* Update Worker */
    $stmt = $conn->prepare(
        "UPDATE Worker
         SET phone = ?, experience = ?, availability = ?
         WHERE user_id = ?"
    );
    $stmt->bind_param("sisi", $phone, $experience, $availability, $user_id);
    $stmt->execute();
    $stmt->close();

    /* Replace skills */
    $stmt = $conn->prepare(
        "DELETE FROM Worker_Skill WHERE worker_id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    if ($skills_input !== "") {
        $skills = explode(",", $skills_input);
        $stmt = $conn->prepare(
            "INSERT INTO Worker_Skill (worker_id, skill)
             VALUES (?, ?)"
        );

        foreach ($skills as $skill) {
            $skill = trim($skill);
            if ($skill !== "") {
                $stmt->bind_param("is", $user_id, $skill);
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    $message = "Profile updated successfully.";
}

/* ===============================
   FETCH PROFILE DATA
================================ */

/* General user */
$stmt = $conn->prepare(
    "SELECT name, email
     FROM General_User
     WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

/* Worker */
$stmt = $conn->prepare(
    "SELECT phone, experience, availability
     FROM Worker
     WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($phone, $experience, $availability);
$stmt->fetch();
$stmt->close();

/* Skills */
$stmt = $conn->prepare(
    "SELECT GROUP_CONCAT(skill SEPARATOR ', ')
     FROM Worker_Skill
     WHERE worker_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($skills);
$stmt->fetch();
$stmt->close();

/* ===============================
   FETCH REVIEWS ABOUT WORKER
================================ */
$stmt = $conn->prepare(
    "SELECT 
        r.rating,
        r.comment,
        r.created_at,
        g.name AS reviewer_name
     FROM Review r
     JOIN General_User g
        ON r.reviewer_id = g.user_id
     WHERE r.reviewed_id = ?
     ORDER BY r.created_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reviews = $stmt->get_result();
?>

<?php include "navbar.php"; ?>

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
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Experience (years)</label>
                                <input type="number" name="experience" class="form-control" min="0" value="<?= htmlspecialchars($experience ?? 0) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Availability</label>
                                <input type="text" name="availability" class="form-control" value="<?= htmlspecialchars($availability ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Skills (comma separated)</label>
                                <input type="text" name="skills" class="form-control" placeholder="moping, dusting, cooking" value="<?= htmlspecialchars($skills ?? '') ?>" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary w-100" style="background-color: #6c16be; border: none;">Update Profile</button>
                        </form>
                    </div>
                </div>

                <!-- Saved Profile Section -->
                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light h-100">
                        <h4 class="mb-3">Saved Profile</h4>
                        <ul class="list-group mb-4">
                            <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($name) ?></li>
                            <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($email) ?></li>
                            <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($phone ?? '-') ?></li>
                            <li class="list-group-item"><strong>Experience:</strong> <?= htmlspecialchars($experience ?? '-') ?> years</li>
                            <li class="list-group-item"><strong>Availability:</strong> <?= htmlspecialchars($availability ?? '-') ?></li>
                            <li class="list-group-item"><strong>Skills:</strong> <?= htmlspecialchars($skills ?? '-') ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <hr class="my-5">
            <h3 class="mb-3">Reviews</h3>
            
            <?php if ($reviews->num_rows === 0): ?>
                <p class="text-muted">No reviews yet.</p>
            <?php else: ?>
                <div class="list-group">
                <?php while ($row = $reviews->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?= htmlspecialchars($row['reviewer_name']) ?></h5>
                            <small class="text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                        </div>
                        <p class="mb-1 text-warning">
                            <?php for($i=0; $i<$row['rating']; $i++) echo '⭐'; ?>
                            (<?= htmlspecialchars($row['rating']) ?>/5)
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


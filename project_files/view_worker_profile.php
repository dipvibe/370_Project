<?php
include "auth.php";
include "connection.php";

/* ===============================
   EMPLOYER OR ADMIN ONLY
================================ */
if (!in_array($_SESSION['role'], ['employer', 'administrator'])) {
    header("Location: welcome.php");
    exit;
}

if (!isset($_GET['worker_id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$worker_id = (int)$_GET['worker_id'];

/* ===============================
   FETCH WORKER BASIC INFO
================================ */
$stmt = $conn->prepare(
    "SELECT 
        g.name,
        g.email,
        w.phone,
        w.experience,
        w.availability
     FROM Worker w
     JOIN General_User g ON w.user_id = g.user_id
     WHERE w.user_id = ?"
);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$result = $stmt->get_result();
$worker = $result->fetch_assoc();
$stmt->close();

if (!$worker) {
    echo "Worker not found.";
    exit;
}

/* ===============================
   FETCH SKILLS
================================ */
$stmt = $conn->prepare(
    "SELECT GROUP_CONCAT(skill SEPARATOR ', ')
     FROM Worker_Skill
     WHERE worker_id = ?"
);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$stmt->bind_result($skills);
$stmt->fetch();
$stmt->close();

/* ===============================
   FETCH RATING (FIXED)
================================ */
$stmt = $conn->prepare(
    "SELECT 
        ROUND(AVG(rating), 1) AS avg_rating,
        COUNT(*) AS total_reviews
     FROM Review
     WHERE reviewed_id = ?"
);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$stmt->bind_result($avg_rating, $total_reviews);
$stmt->fetch();
$stmt->close();

/* ===============================
   FETCH REVIEWS LIST (FIXED)
================================ */
$stmt = $conn->prepare(
    "SELECT 
        r.rating,
        r.comment,
        g.name AS reviewer_name,
        r.created_at
     FROM Review r
     JOIN General_User g ON r.reviewer_id = g.user_id
     WHERE r.reviewed_id = ?
     ORDER BY r.created_at DESC"
);
$stmt->bind_param("i", $worker_id);
$stmt->execute();
$reviews = $stmt->get_result();
$stmt->close();
?>

<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Worker Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .profile-card {
          background: white;
          color: #333;
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
        <div class="profile-card mx-auto" style="max-width: 800px;">
            <h2 class="text-center mb-4" style="color: #6c16be;">Worker Profile</h2>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="p-4 border rounded bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?= htmlspecialchars($worker['name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($worker['email']) ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($worker['phone'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Experience:</strong> <?= htmlspecialchars($worker['experience']) ?> years</p>
                                <p><strong>Availability:</strong> <?= htmlspecialchars($worker['availability']) ?></p>
                                <p><strong>Skills:</strong> <?= htmlspecialchars($skills ?? '-') ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <h4 class="mb-0">
                                Rating: <span class="text-warning">⭐ <?= $avg_rating ? $avg_rating : 'N/A' ?> / 5</span>
                                <small class="text-muted fs-6">(<?= $total_reviews ?> reviews)</small>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($total_reviews > 0): ?>
                <h3 class="mb-3">Reviews</h3>
                <div class="list-group">
                    <?php while ($r = $reviews->fetch_assoc()): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?= htmlspecialchars($r['reviewer_name']) ?></h5>
                                <small class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                            </div>
                            <p class="mb-1 text-warning">
                                <?php for($i=0; $i<$r['rating']; $i++) echo '⭐'; ?>
                                (<?= htmlspecialchars($r['rating']) ?>/5)
                            </p>
                            <p class="mb-1"><?= htmlspecialchars($r['comment']) ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary text-center">No reviews yet.</div>
            <?php endif; ?>

            <div class="mt-4 text-center">
                <a href="javascript:history.back()" class="btn btn-outline-secondary">← Back</a>
            </div>
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

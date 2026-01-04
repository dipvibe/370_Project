<?php
include "auth.php";
include "connection.php";

if (!in_array($_SESSION['role'], ['worker', 'employer'])) {
    header("Location: welcome.php");
    exit;
}

if (!isset($_GET['hire_id'])) {
    header("Location: welcome.php");
    exit;
}

$hire_id     = (int)$_GET['hire_id'];
$reviewer_id = $_SESSION['user_id'];
$role        = $_SESSION['role'];
$message     = "";

/* ===============================
   FETCH HIRE INFO
================================ */
$stmt = $conn->prepare(
    "SELECT worker_id, employer_id
     FROM Hires
     WHERE hire_id = ?"
);
$stmt->bind_param("i", $hire_id);
$stmt->execute();
$stmt->bind_result($worker_id, $employer_id);
$stmt->fetch();
$stmt->close();

/* Determine reviewed user */
$reviewed_id = ($role === 'worker') ? $employer_id : $worker_id;

/* ===============================
   PREVENT DUPLICATE REVIEW
================================ */
$check = $conn->prepare(
    "SELECT review_id
     FROM Review
     WHERE hire_id = ? AND reviewer_id = ?"
);
$check->bind_param("ii", $hire_id, $reviewer_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "You have already submitted a review for this job.";
    exit;
}
$check->close();

/* ===============================
   SUBMIT REVIEW
================================ */
if (isset($_POST['submit_review'])) {

    $rating  = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $message = "Rating must be between 1 and 5.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO Review
             (hire_id, reviewer_id, reviewed_id, rating, comment)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iiiis",
            $hire_id,
            $reviewer_id,
            $reviewed_id,
            $rating,
            $comment
        );
        $stmt->execute();
        $stmt->close();

        $message = "Review submitted successfully.";
    }
}
?>

<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Write Review</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .review-card {
          background: white;
          color: #333;
          padding: 30px;
          border-radius: 10px;
          box-shadow: 0 0 15px rgba(0,0,0,0.2);
          max-width: 600px;
          margin: 0 auto;
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
        <div class="review-card">
            <h2 class="text-center mb-4" style="color: #6c16be;">Write Review</h2>

            <?php if ($message): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Rating (1–5)</label>
                    <select name="rating" class="form-select" required>
                        <option value="">Select Rating</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Comment</label>
                    <textarea name="comment" class="form-control" rows="5" placeholder="Share your experience working with this person..." required></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="submit_review" class="btn btn-primary" style="background-color: #6c16be; border: none;">Submit Review</button>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
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

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

<h2>Write Review</h2>

<?php if ($message): ?>
<p style="color:green"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Rating (1–5)</label><br>
    <select name="rating" required>
        <option value="">Select</option>
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
    </select><br><br>

    <label>Comment</label><br>
    <textarea name="comment" rows="4" cols="40"
              placeholder="Write your experience..." required></textarea><br><br>

    <button type="submit" name="submit_review">Submit Review</button>
</form>

<br>
<a href="javascript:history.back()">← Back</a>

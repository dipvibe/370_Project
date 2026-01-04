<?php
include "auth.php";
include "connection.php";

/* ===============================
   EMPLOYER OR ADMIN ONLY
================================ */
if (!in_array($_SESSION['role'], ['employer', 'admin'])) {
    header("Location: welcome.php");
    exit;
}

if (!isset($_GET['worker_id'])) {
    header("Location: welcome.php");
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

<h2>Worker Profile</h2>
<hr>

<ul>
    <li><strong>Name:</strong> <?= htmlspecialchars($worker['name']) ?></li>
    <li><strong>Email:</strong> <?= htmlspecialchars($worker['email']) ?></li>
    <li><strong>Phone:</strong> <?= htmlspecialchars($worker['phone'] ?? '-') ?></li>
    <li><strong>Experience:</strong> <?= htmlspecialchars($worker['experience']) ?> years</li>
    <li><strong>Availability:</strong> <?= htmlspecialchars($worker['availability']) ?></li>
    <li><strong>Skills:</strong> <?= htmlspecialchars($skills ?? '-') ?></li>
    <li>
        <strong>Rating:</strong>
        <?= $avg_rating ? $avg_rating : 'N/A' ?> / 5
        (<?= $total_reviews ?> reviews)
    </li>
</ul>

<?php if ($total_reviews > 0): ?>
    <hr>
    <h3>Reviews</h3>
    <ul>
        <?php while ($r = $reviews->fetch_assoc()): ?>
            <li style="margin-bottom:10px;">
                ⭐ <?= htmlspecialchars($r['rating']) ?>/5 —
                <strong><?= htmlspecialchars($r['reviewer_name']) ?></strong><br>
                <?= htmlspecialchars($r['comment']) ?>
            </li>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>

<br>
<a href="javascript:history.back()">← Back</a>

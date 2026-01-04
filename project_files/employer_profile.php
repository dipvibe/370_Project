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

<?php include "navbar.php"; ?>

<h2>My Profile</h2>

<?php if ($message): ?>
<p style="color:green"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<hr>

<h3>Update Profile</h3>

<form method="POST">
    Name:<br>
    <input type="text" name="name" required><br><br>

    Email:<br>
    <input type="email" name="email" required><br><br>

    Phone:<br>
    <input type="text" name="phone" placeholder="Enter new phone number" required><br><br>

    <button type="submit" name="update_profile">Update Profile</button>
</form>

<hr>

<h3>Saved Profile</h3>
<ul>
    <li><strong>Name:</strong> <?= htmlspecialchars($name) ?></li>
    <li><strong>Email:</strong> <?= htmlspecialchars($email) ?></li>
    <li><strong>Phone:</strong> <?= htmlspecialchars($phone ?? '-') ?></li>
</ul>

<hr>

<h3>Reviews</h3>

<?php if ($avg_rating): ?>
<p><strong>Average Rating:</strong> <?= $avg_rating ?> / 5</p>
<?php else: ?>
<p>No reviews yet.</p>
<?php endif; ?>

<?php if ($reviews->num_rows > 0): ?>
<ul>
<?php while ($row = $reviews->fetch_assoc()): ?>
    <li>
        ⭐ <?= htmlspecialchars($row['rating']) ?>/5  
        — <strong><?= htmlspecialchars($row['reviewer_name']) ?></strong><br>
        <?= htmlspecialchars($row['comment']) ?>
    </li>
<?php endwhile; ?>
</ul>
<?php endif; ?>

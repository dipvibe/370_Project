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
    <input type="text" name="phone" required><br><br>

    Experience (years):<br>
    <input type="number" name="experience" min="0" required><br><br>

    Availability:<br>
    <input type="text" name="availability" required><br><br>

    Skills (comma separated):<br>
    <input type="text" name="skills"
           placeholder="moping, dusting, cooking" required><br><br>

    <button type="submit" name="update_profile">Update Profile</button>
</form>

<hr>

<h3>Saved Profile</h3>
<ul>
    <li><strong>Name:</strong> <?= htmlspecialchars($name) ?></li>
    <li><strong>Email:</strong> <?= htmlspecialchars($email) ?></li>
    <li><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></li>
    <li><strong>Experience:</strong> <?= htmlspecialchars($experience ?? '-') ?> years</li>
    <li><strong>Availability:</strong> <?= htmlspecialchars($availability ?? '-') ?></li>
    <li><strong>Skills:</strong> <?= htmlspecialchars($skills ?? '-') ?></li>
</ul>

<hr>

<h3>Reviews</h3>

<?php if ($reviews->num_rows === 0): ?>
    <p>No reviews yet.</p>
<?php else: ?>
<table border="1" cellpadding="8">
<tr>
    <th>Reviewer</th>
    <th>Rating</th>
    <th>Comment</th>
    <th>Date</th>
</tr>

<?php while ($row = $reviews->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['reviewer_name']) ?></td>
    <td><?= htmlspecialchars($row['rating']) ?>/5</td>
    <td><?= htmlspecialchars($row['comment']) ?></td>
    <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))) ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>


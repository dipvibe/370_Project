<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'employer') {
    header("Location: welcome.php");
    exit;
}

if (!isset($_GET['job_id'])) {
    header("Location: my_jobs.php");
    exit;
}

$job_id = (int)$_GET['job_id'];

/* ===============================
   CHECK IF JOB ALREADY HIRED
================================ */
$check = $conn->prepare(
    "SELECT hire_id FROM Hires WHERE job_id = ?"
);
$check->bind_param("i", $job_id);
$check->execute();
$check->bind_result($hire_id);
$check->fetch();
$check->close();

/* ===============================
   FETCH APPLICATIONS FOR THIS JOB ONLY
================================ */
$stmt = $conn->prepare(
    "SELECT 
        r.request_id,
        r.status,
        r.worker_id,
        g.name AS worker_name,
        GROUP_CONCAT(jwt.work_type SEPARATOR ', ') AS work_types,
        j.area
     FROM Job_Request r
     JOIN General_User g ON r.worker_id = g.user_id
     JOIN Job_List j ON r.job_id = j.job_id
     LEFT JOIN Job_Work_Type jwt ON j.job_id = jwt.job_id
     WHERE r.job_id = ?
     GROUP BY r.request_id
     ORDER BY g.name ASC"
);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include "navbar.php"; ?>

<h2>Applications for This Job</h2>

<table border="1" cellpadding="8">
<tr>
    <th>Worker</th>
    <th>Area</th>
    <th>Work Types</th>
    <th>Status</th>
    <th>Profile</th>
    <th>Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['worker_name']) ?></td>
    <td><?= htmlspecialchars($row['area']) ?></td>
    <td><?= htmlspecialchars($row['work_types'] ?? '-') ?></td>
    <td><?= htmlspecialchars($row['status']) ?></td>

    <td>
        <a href="view_worker_profile.php?worker_id=<?= $row['worker_id'] ?>">
            View Profile
        </a>
    </td>

    <td>
    <?php if (!$hire_id && $row['status'] === 'pending'): ?>

        <!-- HIRE -->
        <form method="POST" action="hire_worker.php" style="display:inline;">
            <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
            <input type="hidden" name="job_id" value="<?= $job_id ?>">
            <input type="hidden" name="worker_id" value="<?= $row['worker_id'] ?>">
            <button type="submit" name="hire">Hire</button>
        </form>

    <?php elseif ($row['status'] === 'accepted'): ?>

        <!-- HIRED + UNHIRE -->
        <span style="color:green;font-weight:bold;">Hired</span>

        <form method="POST" action="unhire_worker.php" style="display:inline;">
            <input type="hidden" name="job_id" value="<?= $job_id ?>">
            <button type="submit" style="margin-left:8px;">Unhire</button>
        </form>

    <?php else: ?>
        —
    <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

<br>
<a href="my_jobs.php">← Back to My Jobs</a>

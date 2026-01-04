<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'worker') {
    header("Location: welcome.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT 
        j.job_id,
        j.area,
        j.schedule,
        r.status,
        GROUP_CONCAT(jwt.work_type SEPARATOR ', ') AS work_types,
        h.hire_id,
        j.employer_id
     FROM Job_Request r
     JOIN Job_List j ON r.job_id = j.job_id
     LEFT JOIN Job_Work_Type jwt ON j.job_id = jwt.job_id
     LEFT JOIN Hires h 
        ON j.job_id = h.job_id 
       AND h.worker_id = r.worker_id
     WHERE r.worker_id = ?
     GROUP BY j.job_id, r.status, h.hire_id, j.employer_id
     ORDER BY j.area ASC"
);

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include "navbar.php"; ?>

<h2>My Job Applications</h2>

<table border="1" cellpadding="8">
<tr>
    <th>Area</th>
    <th>Work Types</th>
    <th>Schedule</th>
    <th>Status</th>
    <th>Review</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['area']) ?></td>
    <td><?= htmlspecialchars($row['work_types'] ?? '-') ?></td>
    <td><?= htmlspecialchars($row['schedule']) ?></td>
    <td><?= htmlspecialchars($row['status']) ?></td>

    <td>
        <?php if ($row['status'] === 'accepted' && $row['hire_id']): ?>
            <a href="write_review.php?hire_id=<?= $row['hire_id'] ?>">
                Write Review
            </a>
        <?php else: ?>
            —
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>


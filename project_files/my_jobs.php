<?php
include "auth.php";
include "connection.php";

/* ===============================
   EMPLOYER ONLY
================================ */
if ($_SESSION['role'] !== 'employer') {
    header("Location: welcome.php");
    exit;
}

/* ===============================
   FETCH EMPLOYER JOBS + HIRE STATUS
================================ */
$stmt = $conn->prepare(
    "SELECT 
        j.job_id,
        j.area,
        j.schedule,
        j.salary_offer,
        GROUP_CONCAT(jwt.work_type SEPARATOR ', ') AS work_types,
        h.hire_id,
        u.name AS worker_name
     FROM Job_List j
     LEFT JOIN Job_Work_Type jwt
        ON j.job_id = jwt.job_id
     LEFT JOIN Hires h 
        ON j.job_id = h.job_id
     LEFT JOIN General_User u 
        ON h.worker_id = u.user_id
     WHERE j.employer_id = ?
     GROUP BY j.job_id
     ORDER BY j.area ASC"
);

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include "navbar.php"; ?>

<h2>My Posted Jobs</h2>

<table border="1" cellpadding="8" cellspacing="0">
<tr>
    <th>Area</th>
    <th>Work Types</th>
    <th>Schedule</th>
    <th>Salary</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php while ($job = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($job['area']) ?></td>
    <td><?= htmlspecialchars($job['work_types'] ?? '-') ?></td>
    <td><?= htmlspecialchars($job['schedule']) ?></td>
    <td><?= htmlspecialchars($job['salary_offer']) ?></td>

    <!-- STATUS -->
    <td>
        <?php if ($job['hire_id']): ?>
            Hired (<?= htmlspecialchars($job['worker_name']) ?>)
        <?php else: ?>
            Open
        <?php endif; ?>
    </td>

    <!-- ACTIONS -->
    <td>
          <a href="view_application.php?job_id=<?= $job['job_id'] ?>">
          View Applications
          </a>

        <?php if ($job['hire_id']): ?>
          |
          <a href="employer_payments.php?job_id=<?= $job['job_id'] ?>">
              Payments
          </a>
          |
          <a href="write_review.php?hire_id=<?= $job['hire_id'] ?>">
            Write Review
          </a>
        <?php endif; ?>
    </td>

</tr>
<?php endwhile; ?>
</table>

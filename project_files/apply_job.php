<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'worker') {
    header("Location: welcome.php");
    exit;
}

$area = $_GET['area'] ?? '';

/* ===============================
   APPLY TO JOB (SERVER-SIDE BLOCK)
================================ */
if (isset($_POST['apply'])) {

    $job_id = $_POST['job_id'];
    $worker_id = $_SESSION['user_id'];

    // Block apply if job already hired
    $check = $conn->prepare(
        "SELECT hire_id FROM Hires WHERE job_id = ?"
    );
    $check->bind_param("i", $job_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $stmt = $conn->prepare(
            "INSERT IGNORE INTO Job_Request (job_id, worker_id)
             VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $job_id, $worker_id);
        $stmt->execute();
        $stmt->close();
    }

    $check->close();
}

/* ===============================
   FETCH JOBS (MULTI WORK TYPES)
================================ */
if ($area !== '') {
    $stmt = $conn->prepare(
        "SELECT 
            j.job_id,
            j.employer_id,
            j.area,
            j.schedule,
            j.salary_offer,
            g.name AS employer_name,
            GROUP_CONCAT(jwt.work_type SEPARATOR ', ') AS work_types,
            r.request_id,
            h.hire_id
         FROM Job_List j
         JOIN General_User g
           ON j.employer_id = g.user_id
         LEFT JOIN Job_Work_Type jwt
           ON j.job_id = jwt.job_id
         LEFT JOIN Job_Request r
           ON j.job_id = r.job_id
          AND r.worker_id = ?
         LEFT JOIN Hires h
           ON j.job_id = h.job_id
         WHERE j.area LIKE ?
           AND g.is_banned = 0
         GROUP BY j.job_id
         ORDER BY j.area ASC"
    );
    $like = "%$area%";
    $stmt->bind_param("is", $_SESSION['user_id'], $like);
    $stmt->execute();
    $jobs = $stmt->get_result();
} else {
    $stmt = $conn->prepare(
        "SELECT 
            j.job_id,
            j.employer_id,
            j.area,
            j.schedule,
            j.salary_offer,
            g.name AS employer_name,
            GROUP_CONCAT(jwt.work_type SEPARATOR ', ') AS work_types,
            r.request_id,
            h.hire_id
         FROM Job_List j
         JOIN General_User g
           ON j.employer_id = g.user_id
         LEFT JOIN Job_Work_Type jwt
           ON j.job_id = jwt.job_id
         LEFT JOIN Job_Request r
           ON j.job_id = r.job_id
          AND r.worker_id = ?
         LEFT JOIN Hires h
           ON j.job_id = h.job_id
         WHERE g.is_banned = 0
         GROUP BY j.job_id
         ORDER BY j.area ASC"
    );
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $jobs = $stmt->get_result();
}
?>

<?php include "navbar.php"; ?>

<h2>Available Jobs</h2>

<form method="GET">
    <input type="text" name="area" placeholder="Search by area"
           value="<?= htmlspecialchars($area) ?>">
    <button type="submit">Search</button>
</form>

<br>

<table border="1" cellpadding="8">
<tr>
    <th>Employer</th>
    <th>Area</th>
    <th>Work Types</th>
    <th>Schedule</th>
    <th>Salary</th>
    <th>Action</th>
</tr>

<?php if ($jobs->num_rows === 0): ?>
<tr>
    <td colspan="6">No jobs found</td>
</tr>
<?php endif; ?>

<?php while ($job = $jobs->fetch_assoc()): ?>
<tr>
    <td>
        <a href="employer_profile.php?employer_id=<?= $job['employer_id'] ?>">
            <?= htmlspecialchars($job['employer_name']) ?>
        </a>
    </td>
    <td><?= htmlspecialchars($job['area']) ?></td>
    <td><?= htmlspecialchars($job['work_types'] ?? '-') ?></td>
    <td><?= htmlspecialchars($job['schedule']) ?></td>
    <td><?= htmlspecialchars($job['salary_offer']) ?></td>
    <td>
        <?php if ($job['hire_id']): ?>
            <span style="color:red;font-weight:bold;">Filled</span>

        <?php elseif ($job['request_id']): ?>
            <button style="background-color:green;color:white;" disabled>
                Applied
            </button>

        <?php else: ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                <button type="submit" name="apply">Apply</button>
            </form>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>




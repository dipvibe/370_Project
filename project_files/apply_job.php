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

    $job_id    = (int)$_POST['job_id'];
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
   FETCH JOBS
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
            MAX(r.request_id) AS request_id,
            MAX(h.hire_id) AS hire_id
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
            MAX(r.request_id) AS request_id,
            MAX(h.hire_id) AS hire_id
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Find Jobs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .jobs-card {
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
        <div class="jobs-card mx-auto">
            <h2 class="text-center mb-4" style="color: #6c16be;">Available Jobs</h2>

            <form method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="area" class="form-control" placeholder="Search by area (e.g. Dhanmondi)" value="<?= htmlspecialchars($area) ?>">
                    <button type="submit" class="btn btn-primary" style="background-color: #6c16be; border: none;">Search</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Employer</th>
                            <th>Area</th>
                            <th>Work Types</th>
                            <th>Schedule</th>
                            <th>Salary</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($jobs->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No jobs found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>

                    <?php while ($job = $jobs->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <!-- ✅ FIXED PROFILE LINK -->
                                <a href="view_employer_profile.php?employer_id=<?= $job['employer_id'] ?>" class="text-decoration-none fw-bold" style="color: #6c16be;">
                                    <?= htmlspecialchars($job['employer_name']) ?>
                                </a>
                            </td>

                            <td><?= htmlspecialchars($job['area']) ?></td>
                            <td><?= htmlspecialchars($job['work_types'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($job['schedule']) ?></td>
                            <td><?= htmlspecialchars($job['salary_offer']) ?></td>

                            <td>
                                <?php if ($job['hire_id']): ?>
                                    <span class="badge bg-danger">Filled</span>

                                <?php elseif ($job['request_id']): ?>
                                    <button class="btn btn-sm btn-success" disabled>
                                        Applied
                                    </button>

                                <?php else: ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                                        <button type="submit" name="apply" class="btn btn-sm btn-primary" style="background-color: #6c16be; border: none;">Apply</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
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
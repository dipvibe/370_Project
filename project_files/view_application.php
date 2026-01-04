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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Job Applications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .applications-card {
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
      .table thead {
          background-color: #6c16be;
          color: white;
      }
  </style>
</head>
<body>

<div class="dashboard-hero">
    <div class="container">
        <div class="applications-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="color: #6c16be;">Applications for This Job</h2>
                <a href="my_jobs.php" class="btn btn-outline-secondary">← Back to My Jobs</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Worker</th>
                            <th>Area</th>
                            <th>Work Types</th>
                            <th>Status</th>
                            <th>Profile</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['worker_name']) ?></td>
                            <td><?= htmlspecialchars($row['area']) ?></td>
                            <td><?= htmlspecialchars($row['work_types'] ?? '-') ?></td>
                            <td>
                                <?php if ($row['status'] === 'accepted'): ?>
                                    <span class="badge bg-success">Accepted</span>
                                <?php elseif ($row['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <a href="view_worker_profile.php?worker_id=<?= $row['worker_id'] ?>" class="btn btn-sm btn-info text-white">
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
                                    <button type="submit" name="hire" class="btn btn-sm btn-success">Hire</button>
                                </form>

                            <?php elseif ($row['status'] === 'accepted'): ?>

                                <!-- HIRED + UNHIRE -->
                                <span class="text-success fw-bold me-2">Hired</span>

                                <form method="POST" action="unhire_worker.php" style="display:inline;">
                                    <input type="hidden" name="job_id" value="<?= $job_id ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Unhire</button>
                                </form>

                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($result->num_rows === 0): ?>
                <p class="text-center text-muted mt-3">No applications received for this job yet.</p>
            <?php endif; ?>
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

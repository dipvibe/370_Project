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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Posted Jobs</title>
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
      .table thead {
          background-color: #6c16be;
          color: white;
      }
  </style>
</head>
<body>

<div class="dashboard-hero">
    <div class="container">
        <div class="jobs-card">
            <h2 class="text-center mb-4" style="color: #6c16be;">My Posted Jobs</h2>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Work Types</th>
                            <th>Schedule</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($job = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($job['area']) ?></td>
                            <td><?= htmlspecialchars($job['work_types'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($job['schedule']) ?></td>
                            <td><?= htmlspecialchars($job['salary_offer']) ?></td>

                            <!-- STATUS -->
                            <td>
                                <?php if ($job['hire_id']): ?>
                                    <span class="badge bg-success">Hired (<?= htmlspecialchars($job['worker_name']) ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Open</span>
                                <?php endif; ?>
                            </td>

                            <!-- ACTIONS -->
                            <td>
                                <a href="view_application.php?job_id=<?= $job['job_id'] ?>" class="btn btn-sm btn-info text-white mb-1">
                                    View Applications
                                </a>

                                <?php if ($job['hire_id']): ?>
                                    <a href="employer_payments.php?job_id=<?= $job['job_id'] ?>" class="btn btn-sm btn-warning text-dark mb-1">
                                        Payments
                                    </a>
                                    <a href="write_review.php?hire_id=<?= $job['hire_id'] ?>" class="btn btn-sm btn-secondary mb-1">
                                        Review
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($result->num_rows === 0): ?>
                <p class="text-center text-muted mt-3">You haven't posted any jobs yet.</p>
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

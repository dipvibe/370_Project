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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Job Applications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .applications-card {
          background: white;
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
        <div class="applications-card mx-auto">
            <h2 class="text-center mb-4" style="color: #6c16be;">My Job Applications</h2>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Area</th>
                            <th>Work Types</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Review</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['area']) ?></td>
                            <td><?= htmlspecialchars($row['work_types'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['schedule']) ?></td>
                            <td>
                                <?php 
                                    $statusClass = 'bg-secondary';
                                    if ($row['status'] === 'accepted') $statusClass = 'bg-success';
                                    elseif ($row['status'] === 'rejected') $statusClass = 'bg-danger';
                                    elseif ($row['status'] === 'pending') $statusClass = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($row['status'])) ?></span>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'accepted' && $row['hire_id']): ?>
                                    <a href="write_review.php?hire_id=<?= $row['hire_id'] ?>" class="btn btn-sm btn-outline-primary">
                                        Write Review
                                    </a>
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
                <div class="text-center py-4 text-muted">
                    <p>You haven't applied to any jobs yet.</p>
                    <a href="apply_job.php" class="btn btn-primary" style="background-color: #6c16be; border: none;">Find Jobs</a>
                </div>
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


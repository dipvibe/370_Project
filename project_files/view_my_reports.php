<?php
include "auth.php";
include "connection.php";

/* ===============================
   FETCH USER'S REPORTS
================================ */
$user_id = (int)$_SESSION['user_id'];

// Get reports where current user is the reporter
$reports = $conn->query(
    "SELECT 
        ir.report_id,
        ir.description,
        ir.status,
        ir.created_at,
        ir.hire_id,
        gu.name AS reported_user_name,
        gu.role AS reported_user_role
     FROM Issue_Report ir
     JOIN General_User gu ON ir.reported_user_id = gu.user_id
     WHERE ir.reporter_id = $user_id
     ORDER BY ir.report_id DESC"
);
?>
<?php
include "auth.php";
include "connection.php";

/* ===============================
   FETCH USER'S REPORTS
================================ */
$user_id = (int)$_SESSION['user_id'];

// Get reports where current user is the reporter
$reports = $conn->query(
    "SELECT 
        ir.report_id,
        ir.description,
        ir.status,
        ir.created_at,
        ir.hire_id,
        gu.name AS reported_user_name,
        gu.role AS reported_user_role
     FROM Issue_Report ir
     JOIN General_User gu ON ir.reported_user_id = gu.user_id
     WHERE ir.reporter_id = $user_id
     ORDER BY ir.report_id DESC"
);
?>
<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Issue Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .reports-card {
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
        <div class="reports-card mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="color: #6c16be;">My Issue Reports</h2>
                <a href="report_issue.php" class="btn btn-primary" style="background-color: #6c16be; border: none;">Report New Issue</a>
            </div>

            <?php if ($reports->num_rows == 0): ?>
                <div class="alert alert-light text-center border">
                    <p class="mb-2">You haven't submitted any reports yet.</p>
                    <p class="mb-0">Click "Report New Issue" above to submit your first report.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Report ID</th>
                                <th>Reported User</th>
                                <th>Description</th>
                                <th>Related Hire</th>
                                <th>Status</th>
                                <th>Submitted On</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($report = $reports->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><strong>#<?= $report['report_id'] ?></strong></td>
                            <td>
                                <?= htmlspecialchars($report['reported_user_name']) ?><br>
                                <small class="text-muted">(<?= ucfirst($report['reported_user_role']) ?>)</small>
                            </td>
                            <td><?= nl2br(htmlspecialchars($report['description'])) ?></td>
                            <td class="text-center">
                                <?= $report['hire_id'] ? 'Hire #' . $report['hire_id'] : '—' ?>
                            </td>
                            <td class="text-center">
                                <?php
                                $status = $report['status'];
                                $badgeClass = 'bg-secondary';
                                if ($status === 'open') {
                                    $badgeClass = 'bg-warning text-dark';
                                } elseif ($status === 'resolved') {
                                    $badgeClass = 'bg-success';
                                } elseif ($status === 'reviewed') {
                                    $badgeClass = 'bg-info text-dark';
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= ucfirst(htmlspecialchars($status)) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?= date('M d, Y h:i A', strtotime($report['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="mt-4 text-center">
                <a href="welcome.php" class="btn btn-link">Back to Home</a>
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
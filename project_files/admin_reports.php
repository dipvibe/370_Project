<?php
include "auth.php";
include "connection.php";

/* ===============================
   ADMIN ONLY
================================ */
if ($_SESSION['role'] !== 'administrator') {
    header("Location: welcome.php");
    exit;
}

/* ===============================
   UPDATE ISSUE STATUS
================================ */
if (isset($_POST['update_status'])) {
    $report_id = (int)$_POST['report_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare(
        "UPDATE Issue_Report SET status = ? WHERE report_id = ?"
    );
    $stmt->bind_param("si", $new_status, $report_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: admin_reports.php");
    exit;
}

/* ===============================
   BAN REPORTED USER
================================ */
if (isset($_POST['ban_user'])) {
    $user_id = (int)$_POST['user_id'];
    $stmt = $conn->prepare(
        "UPDATE General_User SET is_banned = 1 WHERE user_id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: admin_reports.php");
    exit;
}

/* ===============================
   FETCH ALL REPORTS
================================ */
$reports = $conn->query(
    "SELECT 
        ir.report_id,
        ir.description,
        ir.status,
        ir.created_at,
        ir.hire_id,
        reporter.name AS reporter_name,
        reporter.email AS reporter_email,
        reporter.role AS reporter_role,
        reported.user_id AS reported_user_id,
        reported.name AS reported_user_name,
        reported.email AS reported_user_email,
        reported.role AS reported_user_role,
        reported.is_banned AS reported_is_banned
     FROM Issue_Report ir
     JOIN General_User reporter ON ir.reporter_id = reporter.user_id
     JOIN General_User reported ON ir.reported_user_id = reported.user_id
     ORDER BY ir.report_id DESC"
);

/* ===============================
   CALCULATE STATISTICS
================================ */
$stats = $conn->query(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
        SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
     FROM Issue_Report"
)->fetch_assoc();
?>
<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Issue Report Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .admin-card {
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
      .stat-box {
          border-radius: 8px;
          padding: 20px;
          text-align: center;
          color: white;
      }
  </style>
</head>
<body>

<div class="dashboard-hero">
    <div class="container">
        <div class="admin-card">
            <h2 class="text-center mb-4" style="color: #6c16be;">Issue Report Management</h2>

            <!-- Statistics -->
            <div class="row mb-5 g-4">
                <div class="col-md-3">
                    <div class="stat-box" style="background:#667eea;">
                        <h5>Total Reports</h5>
                        <h2 class="fw-bold mb-0"><?= $stats['total'] ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background:#f5576c;">
                        <h5>Open</h5>
                        <h2 class="fw-bold mb-0"><?= $stats['open'] ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background:#4facfe;">
                        <h5>Reviewed</h5>
                        <h2 class="fw-bold mb-0"><?= $stats['reviewed'] ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background:#00f2fe;">
                        <h5>Resolved</h5>
                        <h2 class="fw-bold mb-0"><?= $stats['resolved'] ?></h2>
                    </div>
                </div>
            </div>

            <h3 class="mb-3">All Issue Reports</h3>

            <?php if ($reports->num_rows == 0): ?>
                <div class="alert alert-light text-center border">
                    <p class="mb-0">No reports have been submitted yet.</p>
                </div>
            <?php else: ?>
                <?php while ($report = $reports->fetch_assoc()): ?>
                    <div class="card mb-4 border shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Report #<?= $report['report_id'] ?></strong>
                                <span class="text-muted ms-2">
                                    <?= date('M d, Y h:i A', strtotime($report['created_at'])) ?>
                                </span>
                            </div>
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
                            <span class="badge <?= $badgeClass ?> rounded-pill px-3">
                                <?= ucfirst(htmlspecialchars($status)) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Reporter:</h6>
                                        <p class="mb-1">
                                            <strong><?= htmlspecialchars($report['reporter_name']) ?></strong> 
                                            (<?= ucfirst($report['reporter_role']) ?>)
                                        </p>
                                        <small class="text-muted"><?= htmlspecialchars($report['reporter_email']) ?></small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Description:</h6>
                                        <div class="p-3 bg-light rounded border">
                                            <?= nl2br(htmlspecialchars($report['description'])) ?>
                                        </div>
                                    </div>

                                    <?php if ($report['hire_id']): ?>
                                        <div class="mb-3">
                                            <span class="badge bg-light text-dark border">
                                                🔗 Related Hire: #<?= $report['hire_id'] ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4 border-start">
                                    <div class="alert alert-warning mb-3">
                                        <h6 class="alert-heading">⚠️ Reported User</h6>
                                        <p class="mb-1">
                                            <strong><?= htmlspecialchars($report['reported_user_name']) ?></strong>
                                            (<?= ucfirst($report['reported_user_role']) ?>)
                                        </p>
                                        <small><?= htmlspecialchars($report['reported_user_email']) ?></small>
                                        <?php if ($report['reported_is_banned']): ?>
                                            <div class="mt-2 text-danger fw-bold">
                                                🚫 BANNED
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <form method="POST" class="mb-3">
                                        <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                        <label class="form-label fw-bold">Update Status:</label>
                                        <div class="input-group">
                                            <select name="status" class="form-select">
                                                <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
                                                <option value="reviewed" <?= $status === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                                <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>

                                    <?php if (!$report['reported_is_banned']): ?>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?= $report['reported_user_id'] ?>">
                                            <button type="submit" name="ban_user" 
                                                onclick="return confirm('Are you sure you want to BAN this user?')"
                                                class="btn btn-danger w-100">
                                                🚫 Ban Reported User
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <div class="mt-4 text-center">
                <a href="admin_dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>
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
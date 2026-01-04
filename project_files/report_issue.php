<?php
include "auth.php";
include "connection.php";

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

/* ===============================
   FETCH RELEVANT HIRES (LAST 3)
================================ */
$stmt = $conn->prepare(
    "SELECT 
        h.hire_id,
        h.job_id,
        h.worker_id,
        h.employer_id,
        w.name AS worker_name,
        w.email AS worker_email,
        e.name AS employer_name,
        e.email AS employer_email,
        j.area,
        j.schedule
     FROM Hires h
     JOIN General_User w ON h.worker_id = w.user_id
     JOIN General_User e ON h.employer_id = e.user_id
     JOIN Job_List j ON h.job_id = j.job_id
     WHERE h.worker_id = ? OR h.employer_id = ?
     ORDER BY h.created_at DESC
     LIMIT 3"
);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reportable_users = [];
$hires_list = [];

while ($row = $result->fetch_assoc()) {
    $hires_list[] = $row;
    
    if ($role === 'worker') {
        // Worker reports Employer
        $target_id = $row['employer_id'];
        if (!isset($reportable_users[$target_id])) {
            $reportable_users[$target_id] = [
                'id' => $target_id,
                'name' => $row['employer_name'],
                'email' => $row['employer_email'],
                'role' => 'employer'
            ];
        }
    } elseif ($role === 'employer') {
        // Employer reports Worker
        $target_id = $row['worker_id'];
        if (!isset($reportable_users[$target_id])) {
            $reportable_users[$target_id] = [
                'id' => $target_id,
                'name' => $row['worker_name'],
                'email' => $row['worker_email'],
                'role' => 'worker'
            ];
        }
    }
}
$stmt->close();

/* ===============================
   SUBMIT ISSUE REPORT
================================ */
$message = "";
$error = "";

if (isset($_POST['submit_report'])) {
    $reported_user_id = (int)$_POST['reported_user_id'];
    $hire_id = !empty($_POST['hire_id']) ? (int)$_POST['hire_id'] : NULL;
    $description = trim($_POST['description']);
    
    // Validate that the reported user is in the allowed list
    if (!isset($reportable_users[$reported_user_id])) {
        $error = "Invalid user selected.";
    } elseif (empty($description)) {
        $error = "Please enter a description for your issue.";
    } else {
        if ($hire_id !== NULL) {
            $stmt = $conn->prepare(
                "INSERT INTO Issue_Report (reporter_id, reported_user_id, hire_id, description, status) 
                 VALUES (?, ?, ?, ?, 'open')"
            );
            $stmt->bind_param("iiis", $user_id, $reported_user_id, $hire_id, $description);
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO Issue_Report (reporter_id, reported_user_id, description, status) 
                 VALUES (?, ?, ?, 'open')"
            );
            $stmt->bind_param("iis", $user_id, $reported_user_id, $description);
        }
        
        if ($stmt->execute()) {
            $message = "Your issue has been reported successfully!";
        } else {
            $error = "Error submitting report. Please try again.";
        }
        $stmt->close();
    }
}

/* ===============================
   FETCH CURRENT USER INFO
================================ */
$stmt = $conn->prepare(
    "SELECT name, email FROM General_User WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report an Issue</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .report-card {
          background: white;
          color: #333;
          padding: 30px;
          border-radius: 10px;
          box-shadow: 0 0 15px rgba(0,0,0,0.2);
          max-width: 800px;
          margin: 0 auto;
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
        <div class="report-card">
            <h2 class="text-center mb-4" style="color: #6c16be;">Report an Issue</h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Your Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" disabled>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Report Against User *</label>
                    <select name="reported_user_id" class="form-select" required>
                        <option value="">-- Select User --</option>
                        <?php foreach ($reportable_users as $u): ?>
                            <option value="<?= $u['id'] ?>">
                                <?= htmlspecialchars($u['name']) ?> (<?= ucfirst($u['role']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">You can only report users from your last 3 jobs.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Related to Hire (Optional)</label>
                    <select name="hire_id" class="form-select">
                        <option value="">-- None / General Issue --</option>
                        <?php foreach ($hires_list as $h): ?>
                            <option value="<?= $h['hire_id'] ?>">
                                Hire #<?= $h['hire_id'] ?> - <?= htmlspecialchars($h['area']) ?> (<?= htmlspecialchars($h['schedule']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Issue Description *</label>
                    <textarea name="description" class="form-control" rows="5" required placeholder="Please describe your issue in detail..."></textarea>
                </div>

                <button type="submit" name="submit_report" class="btn btn-primary w-100" style="background-color: #6c16be; border: none;">Submit Report</button>
            </form>

            <hr class="my-4">
            <div class="text-center">
                <a href="view_my_reports.php" class="btn btn-outline-secondary">View My Reports</a>
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
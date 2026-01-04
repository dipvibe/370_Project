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

$message = "";

/* ===============================
   POST JOB
================================ */
if (isset($_POST['submit'])) {

    $salary   = $_POST['salary'];
    $schedule = trim($_POST['schedule']);
    $area     = trim($_POST['area']);
    $house_no = trim($_POST['house_no']);
    $work_types_input = trim($_POST['work_types']);
    $employer_id = $_SESSION['user_id'];

    /* -------------------------------
       1️⃣ INSERT INTO Job_List
    -------------------------------- */
    $stmt = $conn->prepare(
        "INSERT INTO Job_List
         (employer_id, salary_offer, schedule, area, house_no)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "idsss",
        $employer_id,
        $salary,
        $schedule,
        $area,
        $house_no
    );
    $stmt->execute();
    $job_id = $stmt->insert_id;
    $stmt->close();

    /* -------------------------------
       2️⃣ INSERT WORK TYPES
       (comma-separated input)
    -------------------------------- */
    $types = explode(",", $work_types_input);

    $typeStmt = $conn->prepare(
        "INSERT INTO Job_Work_Type (job_id, work_type)
         VALUES (?, ?)"
    );

    foreach ($types as $type) {
        $type = trim($type);
        if ($type !== "") {
            $typeStmt->bind_param("is", $job_id, $type);
            $typeStmt->execute();
        }
    }
    $typeStmt->close();

    $message = "Job posted successfully!";
}
?>

<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Post a Job</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .job-card {
          background: white;
          padding: 40px;
          border-radius: 10px;
          box-shadow: 0 0 15px rgba(0,0,0,0.2);
          max-width: 600px;
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
        <div class="job-card">
            <h2 class="text-center mb-4" style="color: #6c16be;">Post a Job</h2>

            <?php if ($message): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Salary Offer</label>
                    <input type="number" name="salary" step="0.01" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Work Types (comma separated)</label>
                    <input type="text" name="work_types" class="form-control" placeholder="cleaning, cooking, babysitting" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Schedule</label>
                    <input type="text" name="schedule" class="form-control" placeholder="e.g. 9am–3pm" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Area</label>
                    <input type="text" name="area" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">House No</label>
                    <input type="text" name="house_no" class="form-control">
                </div>

                <button type="submit" name="submit" class="btn btn-primary w-100" style="background-color: #6c16be; border: none; padding: 10px; font-size: 18px;">Post Job</button>
            </form>
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


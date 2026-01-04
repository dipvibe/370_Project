<?php
include "auth.php";
include "connection.php";

/* ===============================
   SESSION DATA
================================ */
$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$name    = $_SESSION['username'];
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Welcome</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* ===============================
       HERO BACKGROUND
    ================================ */
    .dashboard-hero {
      background: linear-gradient(
          rgba(0,0,0,0.55),
          rgba(0,0,0,0.55)
        ),
        url('assets/after_login.png') center/cover no-repeat;
      color: white;
      padding: 100px 20px 140px;
    }

    .stat-card {
      border-radius: 14px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.15);
      background: #fff;
    }

    /* ===============================
       FOOTER
    ================================ */
    .footer {
      background-color: #000;
      color: #ddd;
    }
  </style>
</head>
<body>

<?php include "navbar.php"; ?>

<!-- ===============================
     HERO + STATS (SAME SECTION)
================================ -->
<div class="dashboard-hero text-center">

  <h1 class="fw-bold">Welcome, <?= htmlspecialchars($name) ?> 👋</h1>
  <p class="fs-4 mb-5">
    You are logged in as
    <strong><?= ucfirst(htmlspecialchars($role)) ?></strong>
  </p>

  <div class="container">

  <?php if ($role === 'worker'): ?>

    <?php
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Job_Request WHERE worker_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM Job_Request
         WHERE worker_id = ? AND status = 'pending'"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($pending);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM Job_Request
         WHERE worker_id = ? AND status = 'accepted'"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($accepted);
    $stmt->fetch();
    $stmt->close();
    ?>

    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <div class="card stat-card p-4 text-center">
          <h2><?= $total ?></h2>
          <p>Total Applications</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card p-4 text-center">
          <h2><?= $pending ?></h2>
          <p>Pending Applications</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card p-4 text-center">
          <h2><?= $accepted ?></h2>
          <p>Accepted Applications</p>
        </div>
      </div>
    </div>

  <?php elseif ($role === 'employer'): ?>

    <?php
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM Job_List WHERE employer_id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($jobCount);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT COUNT(*)
         FROM Job_Request r
         JOIN Job_List j ON r.job_id = j.job_id
         WHERE j.employer_id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($appCount);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM Hires WHERE employer_id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hireCount);
    $stmt->fetch();
    $stmt->close();
    ?>

    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <div class="card stat-card p-4 text-center">
          <h2><?= $jobCount ?></h2>
          <p>Jobs Posted</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card p-4 text-center">
          <h2><?= $appCount ?></h2>
          <p>Applications Received</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card p-4 text-center">
          <h2><?= $hireCount ?></h2>
          <p>Active Hires</p>
        </div>
      </div>
    </div>

  <?php elseif ($role === 'admin'): ?>

    <div class="text-center">
      <h3 class="mb-3">Administrator Panel</h3>
      <p class="mb-4">
        Manage users, reviews, reports and platform integrity.
      </p>
      <a class="btn btn-light btn-lg" href="admin_dashboard.php">
        Go to Admin Dashboard →
      </a>
    </div>

  <?php endif; ?>

  </div>
</div>

<!-- ===============================
     FOOTER
================================ -->
<footer class="footer text-center p-4">
  <p><strong>Contact Information</strong></p>
  <p>📞 +880 1234 567890</p>
  <p>📧 support@householdnetwork.com</p>
  <p>📍 Dhaka, Bangladesh</p>
  <p class="copyright">
    © 2026 House Hold Network. All rights reserved.
  </p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



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
   BAN / UNBAN USER
================================ */
if (isset($_POST['ban']) || isset($_POST['unban'])) {

    $user_id = (int)$_POST['user_id'];
    $status  = isset($_POST['ban']) ? 1 : 0;

    $stmt = $conn->prepare(
        "UPDATE General_User SET is_banned = ? WHERE user_id = ?"
    );
    $stmt->bind_param("ii", $status, $user_id);
    $stmt->execute();
    $stmt->close();
}

/* ===============================
   STATS
================================ */
$employers = $conn->query(
    "SELECT COUNT(*) AS total FROM General_User WHERE role = 'employer'"
)->fetch_assoc()['total'];

$workers = $conn->query(
    "SELECT COUNT(*) AS total FROM General_User WHERE role = 'worker'"
)->fetch_assoc()['total'];

$jobs = $conn->query(
    "SELECT COUNT(*) AS total FROM Job_List"
)->fetch_assoc()['total'];

/* ===============================
   MODE
================================ */
$type = $_GET['type'] ?? '';

/* ===============================
   FETCH USERS
================================ */
if ($type === 'employer' || $type === 'worker') {
    $users = $conn->prepare(
        "SELECT user_id, name, email, is_banned
         FROM General_User
         WHERE role = ?
         ORDER BY name"
    );
    $users->bind_param("s", $type);
    $users->execute();
    $user_list = $users->get_result();
}

/* ===============================
   FETCH JOBS
================================ */
if ($type === 'jobs') {
    $job_list = $conn->query(
        "SELECT 
            j.job_id,
            j.area,
            j.schedule,
            j.salary_offer,
            g.name AS employer_name,
            h.hire_id
         FROM Job_List j
         JOIN General_User g ON j.employer_id = g.user_id
         LEFT JOIN Hires h ON j.job_id = h.job_id
         ORDER BY j.job_id DESC"
    );
}
?>

<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
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
          background: #f8f9fa;
          border-radius: 8px;
          padding: 20px;
          text-align: center;
          border: 1px solid #dee2e6;
          transition: transform 0.2s;
      }
      .stat-box:hover {
          transform: translateY(-5px);
          box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      }
      .stat-box h4 {
          margin-bottom: 0;
          color: #6c16be;
      }
      .stat-box a {
          text-decoration: none;
          color: inherit;
          display: block;
      }
  </style>
</head>
<body>

<div class="dashboard-hero">
    <div class="container">
        <div class="admin-card">
            <h2 class="text-center mb-4" style="color: #6c16be;">Admin Dashboard</h2>
            
            <!-- Stats -->
            <div class="row mb-5 g-4">
                <div class="col-md-4">
                    <div class="stat-box">
                        <a href="admin_dashboard.php?type=employer">
                            <h5>Employers</h5>
                            <h2 class="fw-bold"><?= $employers ?></h2>
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <a href="admin_dashboard.php?type=worker">
                            <h5>Workers</h5>
                            <h2 class="fw-bold"><?= $workers ?></h2>
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <a href="admin_dashboard.php?type=jobs">
                            <h5>Jobs</h5>
                            <h2 class="fw-bold"><?= $jobs ?></h2>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tables -->
            <?php if ($type === 'employer' || $type === 'worker'): ?>
                <h3 class="mb-3"><?= ucfirst($type) ?> List</h3>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Profile</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($u = $user_list->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($type === 'employer'): ?>
                                        <a href="view_employer_profile.php?employer_id=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-primary">View Profile</a>
                                    <?php else: ?>
                                        <a href="view_worker_profile.php?worker_id=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-primary">View Profile</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['is_banned']): ?>
                                        <span class="badge bg-danger">Banned</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                        <?php if ($u['is_banned']): ?>
                                            <button name="unban" class="btn btn-sm btn-warning">Unban</button>
                                        <?php else: ?>
                                            <button name="ban" class="btn btn-sm btn-danger">Ban</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($type === 'jobs'): ?>
                <h3 class="mb-3">All Posted Jobs</h3>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Employer</th>
                                <th>Area</th>
                                <th>Schedule</th>
                                <th>Salary</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($j = $job_list->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($j['employer_name']) ?></td>
                                <td><?= htmlspecialchars($j['area']) ?></td>
                                <td><?= htmlspecialchars($j['schedule']) ?></td>
                                <td><?= htmlspecialchars($j['salary_offer']) ?></td>
                                <td>
                                    <?php if ($j['hire_id']): ?>
                                        <span class="badge bg-success">Hired</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Open</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>

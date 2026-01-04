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

<?php include "navbar.php"; ?>

<h2>Welcome, <?= htmlspecialchars($name) ?> 👋</h2>
<p>Role: <strong><?= ucfirst($role) ?></strong></p>

<hr>

<?php if ($role === 'worker'): ?>

<?php
/* ===============================
   WORKER STATS
================================ */

// Total applications
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM Job_Request WHERE worker_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

// Pending applications
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM Job_Request 
     WHERE worker_id = ? AND status = 'pending'"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($pending);
$stmt->fetch();
$stmt->close();

// Accepted applications
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

<h3>My Activity</h3>
<ul>
    <li>Total Applications: <strong><?= $total ?></strong></li>
    <li>Pending Applications: <strong><?= $pending ?></strong></li>
    <li>Accepted Applications: <strong><?= $accepted ?></strong></li>
</ul>

<h3>Actions</h3>
<ul>
    <li><a href="worker_profile.php">My Profile</a></li>
    <li><a href="apply_job.php">Find Jobs</a></li>
    <li><a href="applications.php">My Applications</a></li>
    <li><a href="worker_payments.php">My Payments</a></li>
</ul>

<?php elseif ($role === 'employer'): ?>

<?php
/* ===============================
   EMPLOYER STATS
================================ */

// Jobs posted
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM Job_List WHERE employer_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($jobCount);
$stmt->fetch();
$stmt->close();

// Applications received
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

// Active hires
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM Hires WHERE employer_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($hireCount);
$stmt->fetch();
$stmt->close();
?>

<h3>My Overview</h3>
<ul>
    <li>Jobs Posted: <strong><?= $jobCount ?></strong></li>
    <li>Applications Received: <strong><?= $appCount ?></strong></li>
    <li>Active Hires: <strong><?= $hireCount ?></strong></li>
</ul>

<h3>Actions</h3>
<ul>
    <li><a href="employer_profile.php">My Profile</a></li>
    <li><a href="post_job.php">Post a Job</a></li>
    <li><a href="my_jobs.php">My Jobs</a></li>
</ul>

<?php elseif ($role === 'admin'): ?>

<h3>Administrator Panel</h3>
<p>
    Use the Admin Dashboard to manage users and review reports.
</p>
<p>
    <a href="admin_dashboard.php">Go to Admin Dashboard →</a>
</p>

<?php endif; ?>



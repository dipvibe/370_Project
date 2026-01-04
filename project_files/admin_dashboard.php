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

<h2>Admin Dashboard</h2>
<hr>

<!-- ===============================
     STATS
================================ -->
<div style="display:flex; gap:40px; margin-bottom:30px;">
    <h4>
        <a href="admin_dashboard.php?type=employer">
            Employers: <strong><?= $employers ?></strong>
        </a>
    </h4>

    <h4>
        <a href="admin_dashboard.php?type=worker">
            Workers: <strong><?= $workers ?></strong>
        </a>
    </h4>

    <h4>
        <a href="admin_dashboard.php?type=jobs">
            Jobs: <strong><?= $jobs ?></strong>
        </a>
    </h4>
</div>

<!-- ===============================
     USER LIST
================================ -->
<?php if ($type === 'employer' || $type === 'worker'): ?>

<h3><?= ucfirst($type) ?> List</h3>

<table border="1" cellpadding="8">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Profile</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while ($u = $user_list->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($u['name']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>

    <td>
        <?php if ($type === 'employer'): ?>
            <a href="view_employer_profile.php?employer_id=<?= $u['user_id'] ?>">
                View Profile
            </a>
        <?php else: ?>
            <a href="view_worker_profile.php?worker_id=<?= $u['user_id'] ?>">
                View Profile
            </a>
        <?php endif; ?>
    </td>

    <td>
        <?= $u['is_banned']
            ? '<span style="color:red">Banned</span>'
            : 'Active'
        ?>
    </td>

    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
            <?php if ($u['is_banned']): ?>
                <button name="unban">Unban</button>
            <?php else: ?>
                <button name="ban">Ban</button>
            <?php endif; ?>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php endif; ?>

<!-- ===============================
     JOB LIST
================================ -->
<?php if ($type === 'jobs'): ?>

<h3>All Posted Jobs</h3>

<table border="1" cellpadding="8">
<tr>
    <th>Employer</th>
    <th>Area</th>
    <th>Schedule</th>
    <th>Salary</th>
    <th>Status</th>
</tr>

<?php while ($j = $job_list->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($j['employer_name']) ?></td>
    <td><?= htmlspecialchars($j['area']) ?></td>
    <td><?= htmlspecialchars($j['schedule']) ?></td>
    <td><?= htmlspecialchars($j['salary_offer']) ?></td>
    <td>
        <?= $j['hire_id']
            ? '<span style="color:green;">Hired</span>'
            : 'Open'
        ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php endif; ?>

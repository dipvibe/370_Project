<?php
include "auth.php";
include "connection.php";

/* ===============================
   ADMIN ONLY
================================ */
if ($_SESSION['role'] !== 'admin') {
    header("Location: welcome.php");
    exit;
}

/* ===============================
   BAN / UNBAN USER
================================ */
if (isset($_POST['ban'])) {
    $user_id = (int)$_POST['user_id'];

    $stmt = $conn->prepare(
        "UPDATE General_User SET is_banned = 1 WHERE user_id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['unban'])) {
    $user_id = (int)$_POST['user_id'];

    $stmt = $conn->prepare(
        "UPDATE General_User SET is_banned = 0 WHERE user_id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

/* ===============================
   RESOLVE ISSUE
================================ */
if (isset($_POST['resolve_issue'])) {
    $issue_id = (int)$_POST['issue_id'];

    $stmt = $conn->prepare(
        "UPDATE Issue_Report SET status = 'resolved' WHERE issue_id = ?"
    );
    $stmt->bind_param("i", $issue_id);
    $stmt->execute();
    $stmt->close();
}

/* ===============================
   FETCH USERS
================================ */
$users = $conn->query(
    "SELECT user_id, name, email, role, is_banned
     FROM General_User
     ORDER BY role, name"
);

/* ===============================
   FETCH ISSUES
================================ */
$issues = $conn->query(
    "SELECT 
        i.issue_id,
        i.description,
        i.status,
        g.name AS reported_by
     FROM Issue_Report i
     JOIN General_User g ON i.user_id = g.user_id
     ORDER BY i.issue_id DESC"
);
?>

<?php include "navbar.php"; ?>

<h2>Admin Dashboard</h2>

<hr>

<h3>Users</h3>

<table border="1" cellpadding="8">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while ($u = $users->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($u['name']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= ucfirst($u['role']) ?></td>
    <td>
        <?= $u['is_banned'] ? '<span style="color:red">Banned</span>' : 'Active' ?>
    </td>
    <td>
        <?php if ($u['is_banned']): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                <button name="unban">Unban</button>
            </form>
        <?php else: ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                <button name="ban">Ban</button>
            </form>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

<hr>

<h3>Issue Reports</h3>

<table border="1" cellpadding="8">
<tr>
    <th>Reported By</th>
    <th>Description</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while ($i = $issues->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($i['reported_by']) ?></td>
    <td><?= htmlspecialchars($i['description']) ?></td>
    <td><?= htmlspecialchars($i['status']) ?></td>
    <td>
        <?php if ($i['status'] === 'pending'): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="issue_id" value="<?= $i['issue_id'] ?>">
                <button name="resolve_issue">Mark Resolved</button>
            </form>
        <?php else: ?>
            —
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>
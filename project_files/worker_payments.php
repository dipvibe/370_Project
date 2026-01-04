<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'worker') {
    header("Location: welcome.php");
    exit;
}

/* ===============================
   WORKER CONFIRMS PAYMENT
================================ */
if (isset($_POST['confirm'])) {
    $record_id = $_POST['record_id'];

    $confirm = $conn->prepare(
        "UPDATE Payment_Record
         SET worker_status = 'paid'
         WHERE record_id = ?"
    );
    $confirm->bind_param("i", $record_id);
    $confirm->execute();
}

/* ===============================
   FETCH WORKER PAYMENTS
================================ */
$stmt = $conn->prepare(
    "SELECT pr.record_id, pr.salary, pr.payment_month,
            pr.employer_status, pr.worker_status,
            g.name AS employer_name
     FROM Payment_Record pr
     JOIN Hires h ON pr.hire_id = h.hire_id
     JOIN General_User g ON h.employer_id = g.user_id
     WHERE h.worker_id = ?
     ORDER BY pr.payment_month DESC"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include "navbar.php"; ?>

<h2>My Payments</h2>

<table border="1" cellpadding="8">
<tr>
  <th>Employer</th>
  <th>Month</th>
  <th>Salary</th>
  <th>Employer</th>
  <th>Worker</th>
  <th>Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= htmlspecialchars($row['employer_name']) ?></td>
  <td><?= date('F Y', strtotime($row['payment_month'])) ?></td>
  <td><?= $row['salary'] ?></td>
  <td><?= $row['employer_status'] ?></td>
  <td><?= $row['worker_status'] ?></td>
  <td>
    <?php if ($row['employer_status'] === 'paid' && $row['worker_status'] === 'unpaid'): ?>
      <form method="POST">
        <input type="hidden" name="record_id" value="<?= $row['record_id'] ?>">
        <button name="confirm">Confirm Paid</button>
      </form>
    <?php else: ?>
      —
    <?php endif; ?>
  </td>
</tr>
<?php endwhile; ?>
</table>

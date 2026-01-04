<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'employer') {
    header("Location: welcome.php");
    exit;
}

$currentMonth = date('Y-m-01');

/* ===============================
   CREATE MONTHLY PAYMENT RECORDS
================================ */
$stmt = $conn->prepare(
    "SELECT hire_id
     FROM Hires
     WHERE employer_id = ?"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$hires = $stmt->get_result();

while ($hire = $hires->fetch_assoc()) {
    $check = $conn->prepare(
        "SELECT record_id
         FROM Payment_Record
         WHERE hire_id = ? AND payment_month = ?"
    );
    $check->bind_param("is", $hire['hire_id'], $currentMonth);
    $check->execute();
    $exists = $check->get_result();

    if ($exists->num_rows === 0) {
        // Salary can later be fetched from Job_List if needed
        $salary = 10000;

        $insert = $conn->prepare(
            "INSERT INTO Payment_Record
            (hire_id, payment_month, salary)
            VALUES (?, ?, ?)"
        );
        $insert->bind_param("isd", $hire['hire_id'], $currentMonth, $salary);
        $insert->execute();
    }
}

/* ===============================
   EMPLOYER MARKS PAID
================================ */
if (isset($_POST['paid'])) {
    $record_id = $_POST['record_id'];

    $pay = $conn->prepare(
        "UPDATE Payment_Record
         SET employer_status = 'paid',
             pay_date = CURDATE()
         WHERE record_id = ?"
    );
    $pay->bind_param("i", $record_id);
    $pay->execute();
}

/* ===============================
   FETCH PAYMENT RECORDS
================================ */
$query = $conn->prepare(
    "SELECT pr.record_id, pr.salary, pr.payment_month,
            pr.employer_status, pr.worker_status,
            g.name AS worker_name
     FROM Payment_Record pr
     JOIN Hires h ON pr.hire_id = h.hire_id
     JOIN General_User g ON h.worker_id = g.user_id
     WHERE h.employer_id = ?
     ORDER BY pr.payment_month DESC"
);
$query->bind_param("i", $_SESSION['user_id']);
$query->execute();
$result = $query->get_result();
?>

<?php include "navbar.php"; ?>

<h2>Payments</h2>

<table border="1" cellpadding="8">
<tr>
  <th>Worker</th>
  <th>Month</th>
  <th>Salary</th>
  <th>Employer</th>
  <th>Worker</th>
  <th>Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= htmlspecialchars($row['worker_name']) ?></td>
  <td><?= date('F Y', strtotime($row['payment_month'])) ?></td>
  <td><?= $row['salary'] ?></td>
  <td><?= $row['employer_status'] ?></td>
  <td><?= $row['worker_status'] ?></td>
  <td>
    <?php if ($row['employer_status'] === 'unpaid'): ?>
      <form method="POST">
        <input type="hidden" name="record_id" value="<?= $row['record_id'] ?>">
        <button name="paid">Paid</button>
      </form>
    <?php else: ?>
      —
    <?php endif; ?>
  </td>
</tr>
<?php endwhile; ?>
</table>

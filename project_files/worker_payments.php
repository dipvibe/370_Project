<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'worker') {
    header("Location: welcome.php");
    exit;
}

$currentMonth = date('Y-m-01');

/* ===============================
   CREATE MONTHLY PAYMENT RECORDS (Adapted from Employer Logic)
================================ */
$stmt = $conn->prepare(
    "SELECT hire_id
     FROM Hires
     WHERE worker_id = ?"
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
        $salary = 10000; // Default salary as per logic

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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Payments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .payments-card {
          background: white;
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
  </style>
</head>
<body>

<div class="dashboard-hero">
    <div class="container">
        <div class="payments-card mx-auto">
            <h2 class="text-center mb-4" style="color: #6c16be;">My Payments</h2>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Employer</th>
                            <th>Month</th>
                            <th>Salary</th>
                            <th>Employer Status</th>
                            <th>Worker Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['employer_name']) ?></td>
                            <td><?= date('F Y', strtotime($row['payment_month'])) ?></td>
                            <td><?= htmlspecialchars($row['salary']) ?></td>
                            
                            <!-- Employer Status -->
                            <td>
                                <?php if ($row['employer_status'] === 'paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Unpaid</span>
                                <?php endif; ?>
                            </td>

                            <!-- Worker Status -->
                            <td>
                                <?php if ($row['worker_status'] === 'paid'): ?>
                                    <span class="badge bg-success">Received</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>

                            <!-- Action -->
                            <td>
                                <?php if ($row['employer_status'] === 'paid' && $row['worker_status'] === 'unpaid'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="record_id" value="<?= $row['record_id'] ?>">
                                        <button type="submit" name="confirm" class="btn btn-sm btn-primary" style="background-color: #6c16be; border: none;">
                                            Confirm Receipt
                                        </button>
                                    </form>
                                <?php elseif ($row['employer_status'] === 'paid' && $row['worker_status'] === 'paid'): ?>
                                    <span class="text-success">✓ Completed</span>
                                <?php else: ?>
                                    <span class="text-muted">Waiting for Employer</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($result->num_rows === 0): ?>
                <div class="text-center py-4 text-muted">
                    <p>No payment records found.</p>
                </div>
            <?php endif; ?>
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

<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'employer') {
    header("Location: welcome.php");
    exit;
}

if (!isset($_POST['job_id'])) {
    header("Location: my_jobs.php");
    exit;
}

$job_id = (int)$_POST['job_id'];

/* ===============================
   REMOVE HIRE
================================ */

// Delete hire record
$stmt = $conn->prepare(
    "DELETE FROM Hires WHERE job_id = ?"
);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$stmt->close();

// Reset application status back to pending
$stmt = $conn->prepare(
    "UPDATE Job_Request
     SET status = 'pending'
     WHERE job_id = ? AND status = 'accepted'"
);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$stmt->close();

header("Location: view_application.php?job_id=" . $job_id);
exit;

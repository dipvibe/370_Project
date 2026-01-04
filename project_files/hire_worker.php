<?php
include "auth.php";
include "connection.php";

if ($_SESSION['role'] !== 'employer') {
    header("Location: welcome.php");
    exit;
}

if (!isset($_POST['hire'])) {
    header("Location: my_jobs.php");
    exit;
}

$request_id = (int)$_POST['request_id'];
$job_id     = (int)$_POST['job_id'];
$worker_id  = (int)$_POST['worker_id'];
$employer_id = $_SESSION['user_id'];

/* ===============================
   CHECK IF JOB ALREADY HIRED
================================ */
$check = $conn->prepare(
    "SELECT hire_id FROM Hires WHERE job_id = ?"
);
$check->bind_param("i", $job_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    header("Location: view_application.php?job_id=$job_id");
    exit;
}
$check->close();

/* ===============================
   INSERT INTO HIRES
================================ */
$stmt = $conn->prepare(
    "INSERT INTO Hires (job_id, employer_id, worker_id)
     VALUES (?, ?, ?)"
);
$stmt->bind_param("iii", $job_id, $employer_id, $worker_id);
$stmt->execute();
$stmt->close();

/* ===============================
   UPDATE APPLICATION STATUSES
================================ */

// Accepted worker
$stmt = $conn->prepare(
    "UPDATE Job_Request
     SET status = 'accepted'
     WHERE request_id = ?"
);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$stmt->close();

// Reject all other workers
$stmt = $conn->prepare(
    "UPDATE Job_Request
     SET status = 'rejected'
     WHERE job_id = ? AND request_id != ?"
);
$stmt->bind_param("ii", $job_id, $request_id);
$stmt->execute();
$stmt->close();

header("Location: my_jobs.php");
exit;

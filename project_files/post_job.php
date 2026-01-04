<?php
include "auth.php";
include "connection.php";

/* ===============================
   EMPLOYER ONLY
================================ */
if ($_SESSION['role'] !== 'employer') {
    header("Location: welcome.php");
    exit;
}

$message = "";

/* ===============================
   POST JOB
================================ */
if (isset($_POST['submit'])) {

    $salary   = $_POST['salary'];
    $schedule = trim($_POST['schedule']);
    $area     = trim($_POST['area']);
    $house_no = trim($_POST['house_no']);
    $work_types_input = trim($_POST['work_types']);
    $employer_id = $_SESSION['user_id'];

    /* -------------------------------
       1️⃣ INSERT INTO Job_List
    -------------------------------- */
    $stmt = $conn->prepare(
        "INSERT INTO Job_List
         (employer_id, salary_offer, schedule, area, house_no)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "idsss",
        $employer_id,
        $salary,
        $schedule,
        $area,
        $house_no
    );
    $stmt->execute();
    $job_id = $stmt->insert_id;
    $stmt->close();

    /* -------------------------------
       2️⃣ INSERT WORK TYPES
       (comma-separated input)
    -------------------------------- */
    $types = explode(",", $work_types_input);

    $typeStmt = $conn->prepare(
        "INSERT INTO Job_Work_Type (job_id, work_type)
         VALUES (?, ?)"
    );

    foreach ($types as $type) {
        $type = trim($type);
        if ($type !== "") {
            $typeStmt->bind_param("is", $job_id, $type);
            $typeStmt->execute();
        }
    }
    $typeStmt->close();

    $message = "Job posted successfully!";
}
?>

<?php include "navbar.php"; ?>

<h2>Post a Job</h2>

<?php if ($message): ?>
<p style="color:green"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">

    Salary Offer:<br>
    <input type="number" name="salary" step="0.01" required><br><br>

    Work Types (comma separated):<br>
    <input type="text" name="work_types"
           placeholder="cleaning, cooking, babysitting"
           required><br><br>

    Schedule:<br>
    <input type="text" name="schedule"
           placeholder="e.g. 9am–3pm" required><br><br>

    Area:<br>
    <input type="text" name="area" required><br><br>

    House No:<br>
    <input type="text" name="house_no"><br><br>

    <button type="submit" name="submit">Post Job</button>

</form>


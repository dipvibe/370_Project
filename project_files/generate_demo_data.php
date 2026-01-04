<?php
include "connection.php";

// Helper function to run queries and handle errors
function runQuery($conn, $sql) {
    if (mysqli_query($conn, $sql)) {
        // echo "Success: " . substr($sql, 0, 50) . "...<br>";
    } else {
        echo "Error: " . mysqli_error($conn) . "<br>SQL: $sql<br>";
    }
}

$password = password_hash("1234", PASSWORD_DEFAULT);

echo "<h1>Generating Comprehensive Demo Data...</h1>";

// 1. Create Administrator
$check = mysqli_query($conn, "SELECT user_id FROM General_User WHERE email = 'admin@admin.com'");
if (mysqli_num_rows($check) == 0) {
    $sql = "INSERT INTO General_User (name, email, address, password, role, is_verified) VALUES ('System Admin', 'admin@admin.com', 'Admin HQ', '$password', 'administrator', 1)";
    if (mysqli_query($conn, $sql)) {
        $uid = mysqli_insert_id($conn);
        runQuery($conn, "INSERT INTO Administrator (user_id) VALUES ($uid)");
        echo "Created Admin: admin@admin.com<br>";
    }
}

// 2. Create Employers (5)
$employers = [
    ['name' => 'John Doe', 'email' => 'john@example.com', 'address' => '123 Main St, Dhaka', 'phone' => '01711111111'],
    ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'address' => '456 Park Ave, Dhaka', 'phone' => '01722222222'],
    ['name' => 'Rahim Uddin', 'email' => 'rahim@example.com', 'address' => '789 Lake Rd, Dhaka', 'phone' => '01733333333'],
    ['name' => 'Sarah Khan', 'email' => 'sarah@example.com', 'address' => 'Flat 2B, Dhanmondi 27', 'phone' => '01744444444'],
    ['name' => 'Michael Corleone', 'email' => 'michael@example.com', 'address' => 'Gulshan 2, Road 55', 'phone' => '01755555555'],
];

$employer_ids = [];

foreach ($employers as $emp) {
    $check = mysqli_query($conn, "SELECT user_id FROM General_User WHERE email = '{$emp['email']}'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $employer_ids[] = $row['user_id'];
        continue;
    }

    $sql = "INSERT INTO General_User (name, email, address, password, role, is_verified) VALUES ('{$emp['name']}', '{$emp['email']}', '{$emp['address']}', '$password', 'employer', 1)";
    if (mysqli_query($conn, $sql)) {
        $user_id = mysqli_insert_id($conn);
        $employer_ids[] = $user_id;
        runQuery($conn, "INSERT INTO Employer (user_id, phone) VALUES ($user_id, '{$emp['phone']}')");
        echo "Created Employer: {$emp['name']}<br>";
    }
}

// 3. Create Workers (5)
$workers = [
    ['name' => 'Karim Mia', 'email' => 'karim@worker.com', 'address' => 'Badda, Dhaka', 'phone' => '01811111111', 'exp' => 5, 'avail' => 'Morning', 'skills' => ['Driver', 'Gardener']],
    ['name' => 'Fatima Begum', 'email' => 'fatima@worker.com', 'address' => 'Mirpur, Dhaka', 'phone' => '01822222222', 'exp' => 3, 'avail' => 'Full Time', 'skills' => ['House Helper / Maid', 'Cook / Home Chef']],
    ['name' => 'Abdul Ali', 'email' => 'abdul@worker.com', 'address' => 'Uttara, Dhaka', 'phone' => '01833333333', 'exp' => 8, 'avail' => 'Evening', 'skills' => ['Electrician', 'Plumber']],
    ['name' => 'Rohima Khatun', 'email' => 'rohima@worker.com', 'address' => 'Mohammadpur, Dhaka', 'phone' => '01844444444', 'exp' => 10, 'avail' => 'Full Time', 'skills' => ['Nanny / Babysitter', 'House Helper / Maid']],
    ['name' => 'Sumon Sheikh', 'email' => 'sumon@worker.com', 'address' => 'Rampura, Dhaka', 'phone' => '01855555555', 'exp' => 2, 'avail' => 'On Call', 'skills' => ['Cleaner', 'Mover']],
];

$worker_ids = [];

foreach ($workers as $w) {
    $check = mysqli_query($conn, "SELECT user_id FROM General_User WHERE email = '{$w['email']}'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $worker_ids[] = $row['user_id'];
        continue;
    }

    $sql = "INSERT INTO General_User (name, email, address, password, role, is_verified) VALUES ('{$w['name']}', '{$w['email']}', '{$w['address']}', '$password', 'worker', 1)";
    if (mysqli_query($conn, $sql)) {
        $user_id = mysqli_insert_id($conn);
        $worker_ids[] = $user_id;
        runQuery($conn, "INSERT INTO Worker (user_id, experience, availability, phone) VALUES ($user_id, {$w['exp']}, '{$w['avail']}', '{$w['phone']}')");
        
        foreach ($w['skills'] as $skill) {
            runQuery($conn, "INSERT INTO Worker_Skill (worker_id, skill) VALUES ($user_id, '$skill')");
        }
        echo "Created Worker: {$w['name']}<br>";
    }
}

// 4. Create Jobs
// Ensure we have enough employers and workers
if (count($employer_ids) >= 5 && count($worker_ids) >= 5) {
    
    $jobs_data = [
        // Employer 0 (John)
        ['emp_idx' => 0, 'area' => 'Gulshan', 'schedule' => '9 AM - 5 PM', 'salary' => 15000, 'house' => 'House 10, Road 5', 'types' => ['Driver']],
        ['emp_idx' => 0, 'area' => 'Gulshan', 'schedule' => 'Morning', 'salary' => 5000, 'house' => 'House 10, Road 5', 'types' => ['Gardener']],
        
        // Employer 1 (Jane)
        ['emp_idx' => 1, 'area' => 'Dhanmondi', 'schedule' => 'Full Time', 'salary' => 12000, 'house' => 'Flat 4A, House 20', 'types' => ['House Helper / Maid', 'Cook / Home Chef']],
        
        // Employer 2 (Rahim)
        ['emp_idx' => 2, 'area' => 'Banani', 'schedule' => 'On Call', 'salary' => 1000, 'house' => 'House 55, Block B', 'types' => ['Electrician']],
        
        // Employer 3 (Sarah)
        ['emp_idx' => 3, 'area' => 'Dhanmondi', 'schedule' => '10 AM - 6 PM', 'salary' => 18000, 'house' => 'Road 27, House 50', 'types' => ['Nanny / Babysitter']],
        ['emp_idx' => 3, 'area' => 'Dhanmondi', 'schedule' => 'Weekly', 'salary' => 2000, 'house' => 'Road 27, House 50', 'types' => ['Cleaner']],

        // Employer 4 (Michael)
        ['emp_idx' => 4, 'area' => 'Gulshan', 'schedule' => 'Night Shift', 'salary' => 20000, 'house' => 'Road 55, House 1', 'types' => ['Driver', 'Guard']],
    ];

    $created_job_ids = [];

    foreach ($jobs_data as $j) {
        $emp_id = $employer_ids[$j['emp_idx']];
        $sql = "INSERT INTO Job_List (employer_id, area, schedule, salary_offer, house_no) VALUES ($emp_id, '{$j['area']}', '{$j['schedule']}', {$j['salary']}, '{$j['house']}')";
        if (mysqli_query($conn, $sql)) {
            $job_id = mysqli_insert_id($conn);
            $created_job_ids[] = $job_id;
            foreach ($j['types'] as $type) {
                runQuery($conn, "INSERT INTO Job_Work_Type (job_id, work_type) VALUES ($job_id, '$type')");
            }
        }
    }
    echo "Created " . count($created_job_ids) . " Jobs.<br>";

    // 5. Applications & Hires
    // Job 0 (Driver for John) -> Worker 0 (Karim - Driver) -> Hired
    if (isset($created_job_ids[0])) {
        $jid = $created_job_ids[0];
        $wid = $worker_ids[0];
        $eid = $employer_ids[0];
        
        // Application
        runQuery($conn, "INSERT INTO Job_Request (job_id, worker_id, status) VALUES ($jid, $wid, 'accepted')");
        // Hire
        runQuery($conn, "INSERT INTO Hires (worker_id, job_id, employer_id, joining_date) VALUES ($wid, $jid, $eid, '2025-01-01')");
        $hire_id_1 = mysqli_insert_id($conn);
        
        // Review by Employer
        runQuery($conn, "INSERT INTO Review (hire_id, reviewer_id, reviewed_id, rating, comment) VALUES ($hire_id_1, $eid, $wid, 5, 'Excellent driver, very punctual.')");
    }

    // Job 2 (Maid for Jane) -> Worker 1 (Fatima - Maid) -> Hired
    if (isset($created_job_ids[2])) {
        $jid = $created_job_ids[2];
        $wid = $worker_ids[1];
        $eid = $employer_ids[1];

        runQuery($conn, "INSERT INTO Job_Request (job_id, worker_id, status) VALUES ($jid, $wid, 'accepted')");
        runQuery($conn, "INSERT INTO Hires (worker_id, job_id, employer_id, joining_date) VALUES ($wid, $jid, $eid, '2025-02-01')");
        $hire_id_2 = mysqli_insert_id($conn);

        // Review by Worker
        runQuery($conn, "INSERT INTO Review (hire_id, reviewer_id, reviewed_id, rating, comment) VALUES ($hire_id_2, $wid, $eid, 4, 'Good employer, pays on time.')");
        
        // Issue Report by Employer against Worker
        runQuery($conn, "INSERT INTO Issue_Report (reporter_id, reported_user_id, hire_id, description, status) VALUES ($eid, $wid, $hire_id_2, 'She came late 3 days in a row.', 'open')");
    }

    // Job 3 (Electrician for Rahim) -> Worker 2 (Abdul - Electrician) -> Pending
    if (isset($created_job_ids[3])) {
        $jid = $created_job_ids[3];
        $wid = $worker_ids[2];
        runQuery($conn, "INSERT INTO Job_Request (job_id, worker_id, status) VALUES ($jid, $wid, 'pending')");
    }

    // Job 4 (Nanny for Sarah) -> Worker 3 (Rohima - Nanny) -> Hired
    if (isset($created_job_ids[4])) {
        $jid = $created_job_ids[4];
        $wid = $worker_ids[3];
        $eid = $employer_ids[3];
        
        runQuery($conn, "INSERT INTO Job_Request (job_id, worker_id, status) VALUES ($jid, $wid, 'accepted')");
        runQuery($conn, "INSERT INTO Hires (worker_id, job_id, employer_id, joining_date) VALUES ($wid, $jid, $eid, '2025-03-15')");
        $hire_id_3 = mysqli_insert_id($conn);

        // Issue Report by Worker against Employer
        runQuery($conn, "INSERT INTO Issue_Report (reporter_id, reported_user_id, hire_id, description, status) VALUES ($wid, $eid, $hire_id_3, 'Salary was not paid for last month.', 'open')");
    }

    // Job 5 (Cleaner for Sarah) -> Worker 4 (Sumon - Cleaner) -> Rejected
    if (isset($created_job_ids[5])) {
        $jid = $created_job_ids[5];
        $wid = $worker_ids[4];
        runQuery($conn, "INSERT INTO Job_Request (job_id, worker_id, status) VALUES ($jid, $wid, 'rejected')");
    }
    
    echo "Created Applications, Hires, Reviews, and Reports.<br>";
}

echo "<h3>Done! Demo Data Generated.</h3>";
echo "<p><strong>Admin:</strong> admin@admin.com / 1234</p>";
echo "<p><strong>Employers:</strong> john@example.com, jane@example.com, etc. / 1234</p>";
echo "<p><strong>Workers:</strong> karim@worker.com, fatima@worker.com, etc. / 1234</p>";
?>

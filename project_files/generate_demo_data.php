<?php
include "connection.php";

// Helper function to run queries and handle errors
function runQuery($conn, $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Success: " . substr($sql, 0, 50) . "...<br>";
    } else {
        echo "Error: " . mysqli_error($conn) . "<br>";
    }
}

$password = password_hash("1234", PASSWORD_DEFAULT);

echo "<h1>Generating Demo Data...</h1>";

// 1. Create Employers
$employers = [
    ['name' => 'John Doe', 'email' => 'john@example.com', 'address' => '123 Main St, Dhaka', 'phone' => '01711111111'],
    ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'address' => '456 Park Ave, Dhaka', 'phone' => '01722222222'],
    ['name' => 'Rahim Uddin', 'email' => 'rahim@example.com', 'address' => '789 Lake Rd, Dhaka', 'phone' => '01733333333'],
];

$employer_ids = [];

foreach ($employers as $emp) {
    // Check if exists
    $check = mysqli_query($conn, "SELECT user_id FROM General_User WHERE email = '{$emp['email']}'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $employer_ids[] = $row['user_id'];
        echo "Employer {$emp['name']} already exists.<br>";
        continue;
    }

    // Insert General User
    $sql = "INSERT INTO General_User (name, email, address, password, role) VALUES ('{$emp['name']}', '{$emp['email']}', '{$emp['address']}', '$password', 'employer')";
    if (mysqli_query($conn, $sql)) {
        $user_id = mysqli_insert_id($conn);
        $employer_ids[] = $user_id;
        // Insert Employer
        runQuery($conn, "INSERT INTO Employer (user_id, phone) VALUES ($user_id, '{$emp['phone']}')");
    }
}

// 2. Create Workers
$workers = [
    ['name' => 'Karim Mia', 'email' => 'karim@worker.com', 'address' => 'Badda, Dhaka', 'phone' => '01811111111', 'exp' => 5, 'avail' => 'Morning', 'skills' => ['Driver', 'Gardener']],
    ['name' => 'Fatima Begum', 'email' => 'fatima@worker.com', 'address' => 'Mirpur, Dhaka', 'phone' => '01822222222', 'exp' => 3, 'avail' => 'Full Time', 'skills' => ['House Helper / Maid', 'Cook / Home Chef']],
    ['name' => 'Abdul Ali', 'email' => 'abdul@worker.com', 'address' => 'Uttara, Dhaka', 'phone' => '01833333333', 'exp' => 8, 'avail' => 'Evening', 'skills' => ['Electrician', 'Plumber']],
];

$worker_ids = [];

foreach ($workers as $w) {
    // Check if exists
    $check = mysqli_query($conn, "SELECT user_id FROM General_User WHERE email = '{$w['email']}'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $worker_ids[] = $row['user_id'];
        echo "Worker {$w['name']} already exists.<br>";
        continue;
    }

    // Insert General User
    $sql = "INSERT INTO General_User (name, email, address, password, role) VALUES ('{$w['name']}', '{$w['email']}', '{$w['address']}', '$password', 'worker')";
    if (mysqli_query($conn, $sql)) {
        $user_id = mysqli_insert_id($conn);
        $worker_ids[] = $user_id;
        // Insert Worker
        runQuery($conn, "INSERT INTO Worker (user_id, experience, availability, phone) VALUES ($user_id, {$w['exp']}, '{$w['avail']}', '{$w['phone']}')");
        
        // Insert Skills
        foreach ($w['skills'] as $skill) {
            runQuery($conn, "INSERT INTO Worker_Skill (worker_id, skill) VALUES ($user_id, '$skill')");
        }
    }
}

// 3. Create Jobs
if (!empty($employer_ids)) {
    $jobs_data = [
        ['emp_idx' => 0, 'area' => 'Gulshan', 'schedule' => '9 AM - 5 PM', 'salary' => 15000, 'house' => 'House 10, Road 5', 'types' => ['Driver']],
        ['emp_idx' => 0, 'area' => 'Gulshan', 'schedule' => 'Morning', 'salary' => 5000, 'house' => 'House 10, Road 5', 'types' => ['Gardener']],
        ['emp_idx' => 1, 'area' => 'Dhanmondi', 'schedule' => 'Full Time', 'salary' => 12000, 'house' => 'Flat 4A, House 20', 'types' => ['House Helper / Maid', 'Cook / Home Chef']],
        ['emp_idx' => 2, 'area' => 'Banani', 'schedule' => 'On Call', 'salary' => 1000, 'house' => 'House 55, Block B', 'types' => ['Electrician']],
    ];

    foreach ($jobs_data as $j) {
        if (!isset($employer_ids[$j['emp_idx']])) continue;
        $emp_id = $employer_ids[$j['emp_idx']];

        $sql = "INSERT INTO Job_List (employer_id, area, schedule, salary_offer, house_no) VALUES ($emp_id, '{$j['area']}', '{$j['schedule']}', {$j['salary']}, '{$j['house']}')";
        if (mysqli_query($conn, $sql)) {
            $job_id = mysqli_insert_id($conn);
            echo "Created Job ID: $job_id<br>";
            
            foreach ($j['types'] as $type) {
                runQuery($conn, "INSERT INTO Job_Work_Type (job_id, work_type) VALUES ($job_id, '$type')");
            }

            // 4. Create some applications (Job Requests)
            // Apply worker 0 to job 0
            if ($j['emp_idx'] == 0 && isset($worker_ids[0])) {
                 runQuery($conn, "INSERT INTO Job_Request (job_id, worker_id, status) VALUES ($job_id, {$worker_ids[0]}, 'pending')");
            }
        }
    }
}

echo "<h3>Done! You can now login with:</h3>";
echo "<ul>";
echo "<li>Employer: john@example.com / 1234</li>";
echo "<li>Worker: karim@worker.com / 1234</li>";
echo "</ul>";
?>

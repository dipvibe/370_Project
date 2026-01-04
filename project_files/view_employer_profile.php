<?php
include "auth.php";
include "connection.php";

if (!isset($_GET['employer_id'])) {
    header("Location: welcome.php");
    exit;
}

$employer_id = (int)$_GET['employer_id'];

/* ===============================
   FETCH EMPLOYER INFO
================================ */
$stmt = $conn->prepare(
    "SELECT g.name, g.email, g.address, e.phone
     FROM General_User g
     JOIN Employer e ON g.user_id = e.user_id
     WHERE g.user_id = ?"
);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$stmt->bind_result($name, $email, $address, $phone);
$stmt->fetch();
$stmt->close();

/* ===============================
   FETCH EMPLOYER RATING
================================ */
$stmt = $conn->prepare(
    "SELECT 
        IFNULL(AVG(rating), 0) AS avg_rating,
        COUNT(*) AS total_reviews
     FROM Review
     WHERE reviewed_id = ?"
);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$stmt->bind_result($avg_rating, $total_reviews);
$stmt->fetch();
$stmt->close();
?>

<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employer Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
      .profile-card {
          background: white;
          color: #333;
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
        <div class="profile-card mx-auto" style="max-width: 800px;">
            <h2 class="text-center mb-4" style="color: #6c16be;">Employer Profile</h2>
            
            <div class="p-4 border rounded bg-light">
                <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($phone ?? '-') ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($address ?? '-') ?></p>
                <hr>
                <p>
                    <strong>Rating:</strong> 
                    <span class="text-warning">⭐ <?= number_format($avg_rating, 1) ?></span> / 5
                    <small class="text-muted">(<?= $total_reviews ?> reviews)</small>
                </p>
            </div>

            <div class="mt-4 text-center">
                <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>

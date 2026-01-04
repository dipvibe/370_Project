<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>House Hold Network</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
include "connection.php";
include "navbar.php";

/* ===============================
   FETCH PLATFORM STATISTICS
================================ */

// Count Workers
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM Worker");
$worker_count = mysqli_fetch_assoc($result)['total'];

// Count Employers (from General_User)
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM General_User WHERE role = 'employer'"
);
$employer_count = mysqli_fetch_assoc($result)['total'];

// Count Jobs Completed (Hires)
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM Hires");
$jobs_count = mysqli_fetch_assoc($result)['total'];
?>

<!-- HERO SECTION -->
<div class="hero">
  <div class="hero-overlay">
    <h1>Welcome To House Hold Network</h1>
    <p>
      A secure platform connecting households with trusted service providers
    </p>
  </div>
</div>

<!-- ABOUT SECTION -->
<div class="about-section">
  <h2>What We Are & What We Offer</h2>
  <p>
    <strong>House Hold Network</strong> is a web-based platform designed to simplify
    the process of hiring reliable household service providers in a secure and
    verified environment.
    <br><br>
    Employers can find trusted workers, and workers can get consistent job
    opportunities with transparency and accountability.
  </p>

  <h3>Services Available</h3>
  <ul class="service-list">
    <li>House Helper / Maid</li>
    <li>Cook / Home Chef</li>
    <li>Driver</li>
    <li>Gardener</li>
    <li>Babysitter / Nanny</li>
    <li>Elderly Caretaker</li>
    <li>Cleaner</li>
    <li>Electrician</li>
    <li>Plumber</li>
    <li>Appliance Technician</li>
  </ul>
</div>

<!-- STATISTICS SECTION -->
<div class="stats">
  <div class="stat-box">
    <h2>4.8 ★</h2>
    <p>Platform Rating</p>
  </div>

  <div class="stat-box">
    <h2><?= $worker_count ?>+</h2>
    <p>Registered Workers</p>
  </div>

  <div class="stat-box">
    <h2><?= $employer_count ?>+</h2>
    <p>Active Employers</p>
  </div>

  <div class="stat-box">
    <h2><?= $jobs_count ?>+</h2>
    <p>Jobs Completed</p>
  </div>
</div>

<!-- FOOTER -->
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

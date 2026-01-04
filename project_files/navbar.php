<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$homeLink = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
    ? 'welcome.php'
    : 'index.php';

$role = $_SESSION['role'] ?? null;
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary px-4">
    <a class="navbar-brand fw-bold me-4" href="<?= $homeLink ?>">
        House Hold Network
    </a>

    <!-- ROLE BASED LINKS (BESIDE LOGO) -->
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <div class="d-flex align-items-center gap-3">

            <?php if ($role === 'worker'): ?>
                <a class="nav-link" href="worker_profile.php">My Profile</a>
                <a class="nav-link" href="apply_job.php">Find Jobs</a>
                <a class="nav-link" href="applications.php">Applications</a>

            <?php elseif ($role === 'employer'): ?>
                <a class="nav-link" href="employer_profile.php">My Profile</a>
                <a class="nav-link" href="post_job.php">Post Job</a>
                <a class="nav-link" href="my_jobs.php">My Jobs</a>

            <?php elseif ($role === 'administrator'): ?>
                <a class="nav-link fw-semibold" href="admin_dashboard.php">
                    Admin Dashboard
                </a>
            <?php endif; ?>

        </div>
    <?php endif; ?>

    <!-- RIGHT SIDE -->
    <div class="ms-auto d-flex align-items-center">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <span class="me-3">Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        <?php else: ?>
            <a class="btn btn-outline-primary me-2" href="login.php">Login</a>
            <a class="btn btn-outline-success" href="signup.php">Signup</a>
        <?php endif; ?>
    </div>
</nav>
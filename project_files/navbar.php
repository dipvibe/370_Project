<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$homeLink = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
    ? 'welcome.php'
    : 'index.php';
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary px-4">
    <a class="navbar-brand fw-bold" href="<?= $homeLink ?>">
        House Hold Network
    </a>

    <div class="ms-auto d-flex align-items-center">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrator'): ?>
                <a class="btn btn-outline-dark me-3" href="admin_dashboard.php">
                    Admin Dashboard
                </a>
            <?php endif; ?>

            <span class="me-3">Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a class="btn btn-outline-danger" href="logout.php">Logout</a>

        <?php else: ?>
            <a class="btn btn-outline-primary me-2" href="login.php">Login</a>
            <a class="btn btn-outline-success" href="signup.php">Signup</a>
        <?php endif; ?>
    </div>
</nav>




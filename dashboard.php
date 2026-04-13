<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Dashboard</title>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary p-3 shadow">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">AppDev Dashboard</span>
            <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="card p-5 shadow-lg border-0 bg-white">
            <h1 class="display-4">Hello, <?php echo $_SESSION['fname']; ?>!</h1>
            <p class="lead text-muted">Welcome to your internal portal. Your session is now active.</p>
            <hr>
            <p>You can now manage your profile and view application settings.</p>
        </div>
    </div>
</body>
</html>
<?php
session_start();
include 'config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id, reset_expires FROM user WHERE reset_token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $expires = $row['reset_expires'];

        if (strtotime($expires) < time()) {
            die("Token expired.");
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE user SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?");
            $stmt->bind_param("si", $newPassword, $user_id);
            $stmt->execute();

            header("Location: login.php?reset=success");
            exit;
        }
    } else {
        die("Invalid token.");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container card p-4 shadow-sm" style="max-width: 400px;">
        <h2 class="text-center mb-4">Reset Password</h2>
        <form method="POST">
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="New Password" required></div>
            <button type="submit" class="btn btn-success w-100">Reset Password</button>
        </form>
    </div>
</body>
</html>
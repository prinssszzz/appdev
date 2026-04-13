<?php
session_start();
include 'config.php';
require_once(__DIR__ . '/vendor/autoload.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Save token in DB
        $stmt = $conn->prepare("UPDATE user SET reset_token=?, reset_expires=? WHERE id=?");
        $stmt->bind_param("ssi", $token, $expires, $user_id);
        $stmt->execute();

        // Build reset link
        $resetLink = "http://localhost/Basbas/reset.php?token=" . $token;

        // Use your send-mail.php
        require 'send-mail.php';
        sendResetMail($email, $resetLink);

        $success = "Password reset link sent to your email.";
    } else {
        $error = "Email not found!";
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $secretKey = "6Lf2t7EsAAAAADJ2_bIur1kDVlEaE32lejJ8vVGp"; // from Google

    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}");
    $responseData = json_decode($verifyResponse);

    if (!$responseData->success) {
        $message = "reCAPTCHA verification failed. Please try again.";
    } else {
        // continue with your password reset logic
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container card p-4 shadow-sm" style="max-width: 400px;">
        <h2 class="text-center mb-4">Forgot Password</h2>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        <form method="POST">
            <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Enter your email" required></div>
            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>
    </div>
</body>
</html>
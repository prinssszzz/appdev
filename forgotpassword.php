<?php
session_start();
include 'config.php';
require_once(__DIR__ . '/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $secretKey = $_ENV['RECAPTCHA_SECRET_KEY'];

    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$recaptchaResponse}");

    if ($verifyResponse === false) {
        $error = "Could not verify reCAPTCHA. Please try again.";
    } else {
        $responseData = json_decode($verifyResponse);

        if (!$responseData->success) {
            $error = "reCAPTCHA verification failed. Please try again.";
        } else {
            $email = trim($_POST['email']);

            $stmt = $conn->prepare("SELECT id FROM user WHERE email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_id = $row['id'];

                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

                $stmt = $conn->prepare("UPDATE user SET reset_token=?, reset_expires=? WHERE id=?");
                $stmt->bind_param("ssi", $token, $expires, $user_id);
                $stmt->execute();

                $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                $resetLink = $baseUrl . dirname($_SERVER['REQUEST_URI']) . "/reset.php?token=" . $token;

                require 'send-mail.php';
                sendResetMail($email, $resetLink);

                $success = "Password reset link sent to your email.";
            } else {
                $error = "Email not found!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container card p-4 shadow-sm" style="max-width: 400px;">
        <h2 class="text-center mb-4">Forgot Password</h2>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>"; ?>
        <?php if(isset($success)) echo "<div class='alert alert-success'>" . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . "</div>"; ?>
        <form method="POST">
            <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Enter your email" required></div>
            <div class="d-flex justify-content-center mb-3">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'], ENT_QUOTES, 'UTF-8'); ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>
    </div>
</body>
</html>

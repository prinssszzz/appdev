<?php
include 'config.php';
require_once(__DIR__ . '/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (isset($_POST['signup'])) {
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
            $fname = trim($_POST['firstname']);
            $lname = trim($_POST['lastname']);
            $mname = trim($_POST['middlename']);
            $email = trim($_POST['email']);
            $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $checkStmt = $conn->prepare("SELECT id FROM user WHERE email=?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $error = "An account with this email already exists.";
            } else {
                $stmt = $conn->prepare("INSERT INTO user (firstname, lastname, middlename, email, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $fname, $lname, $mname, $email, $pass);

                if ($stmt->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Sign Up</title>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card mx-auto shadow-sm" style="max-width: 500px;">
            <div class="card-body">
                <h3 class="text-center mb-4">Create Account</h3>
                <?php if(isset($error)) echo "<div class='alert alert-danger'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>"; ?>
                <form method="POST">
                    <div class="mb-3"><input type="text" name="firstname" class="form-control" placeholder="First Name" required></div>
                    <div class="mb-3"><input type="text" name="lastname" class="form-control" placeholder="Last Name" required></div>
                    <div class="mb-3"><input type="text" name="middlename" class="form-control" placeholder="Middle Name"></div>
                    <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                    <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                    <div class="d-flex justify-content-center mb-3">
                        <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'], ENT_QUOTES, 'UTF-8'); ?>"></div>
                    </div>
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                    <button type="submit" name="signup" class="btn btn-primary w-100">Register</button>
                </form>
                <div class="text-center mt-3"><a href="login.php">Already a member? Login</a></div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
session_start();
include 'config.php'; // must define $conn = mysqli_connect(...)
require_once(__DIR__ . '/vendor/autoload.php');

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Google Client setup (for verifying ID token)
$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);

// --- GOOGLE LOGIN HANDLER ---
if (isset($_POST['credential'])) {
    $ticket = $client->verifyIdToken($_POST['credential']);
    if ($ticket) {
        $payload = $ticket->getAttributes()['payload'];
        $email = $payload['email'];
        $fullName = $payload['name'];

        // Split name into first/last
        $parts = explode(" ", $fullName);
        $firstname = $parts[0];
        $lastname  = isset($parts[1]) ? implode(" ", array_slice($parts, 1)) : "";

        // Check if user exists in `user` table
        $stmt = $conn->prepare("SELECT id, firstname FROM user WHERE email=?");
        if ($stmt === false) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Insert new user with dummy password
            $dummyPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
            if ($stmt === false) die("Prepare failed: " . $conn->error);
            $stmt->bind_param("ssss", $firstname, $lastname, $email, $dummyPassword);
            if (!$stmt->execute()) die("Insert failed: " . $stmt->error);
            $user_id = $stmt->insert_id;
        } else {
            $row = $result->fetch_assoc();
            $user_id = $row['id'];
            $firstname = $row['firstname'];
        }

        $_SESSION['id']    = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['fname'] = $firstname;

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Google login failed!";
    }
}

// --- EMAIL/PASSWORD LOGIN HANDLER ---
if (isset($_POST['login'])) {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE email=?");
    if ($stmt === false) die("Prepare failed: " . $conn->error);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['id']    = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['fname'] = $row['firstname'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<style>
    .g_id_signin {
      display: flex;
      justify-content: center;
      margin-top: 1rem;
    }
    .g_id_signin iframe {
      border-radius: 8px !important;
      box-shadow: 0 2px 6px rgba(223, 202, 202, 0.1);
    }
    .fixed-message {
    position: fixed;
    bottom: 20px;
    right: 20px;
    min-width: 250px;
    z-index: 9999;
}   
</style>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container card p-4 shadow-sm" style="max-width: 400px;">
        <h2 class="text-center mb-4">Login</h2>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>"; ?>
        <form method="POST">
            <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            <button type="submit" name="login" class="btn btn-success w-100">Login</button>
        </form>

        <div class="mt-3 text-center">
            <a href="forgotpassword.php">Forgot your password?</a>
        </div>
        <div class="text-center mt-3">
            <small>Don’t have an account? <a href="signup.php">Sign up here</a></small>
        </div>

        <!-- Google Sign-In widget -->
        <div id="g_id_onload"
             data-client_id="<?php echo htmlspecialchars($_ENV['GOOGLE_CLIENT_ID'], ENT_QUOTES, 'UTF-8'); ?>"
             data-login_uri="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>"
             data-auto_prompt="false">
        </div>
        <div class="g_id_signin"
             data-type="standard"
             data-shape="rectangular"
             data-theme="outline"
             data-text="signin_with"
             data-size="large"
             data-logo_alignment="left">
        </div>
    </div>
</body>
</html>
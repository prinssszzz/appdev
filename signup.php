<?php
include 'config.php';
if (isset($_POST['signup'])) {
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];
    $mname = $_POST['middlename'];
    $email = $_POST['email'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO user (firstname, lastname, middlename, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fname, $lname, $mname, $email, $pass);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
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
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="mb-3"><input type="text" name="firstname" class="form-control" placeholder="First Name" required></div>
                    <div class="mb-3"><input type="text" name="lastname" class="form-control" placeholder="Last Name" required></div>
                    <div class="mb-3"><input type="text" name="middlename" class="form-control" placeholder="Middle Name"></div>
                    <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                    <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                     <!--Recaptcha-->
          <div class="d-flex justify-content-center mb-3">
        <div class="g-recaptcha" data-sitekey="6LdJA50sAAAAAH66PGOfAnwuhDo6XMFOUNO-XDoz"></div>
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
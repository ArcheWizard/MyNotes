<?php

include("../PHP/connect.php");
/*require_once '../vendor/google/auth/autoload.php';

// Initialize Google Client
$client = new Google_Client();
$client->setClientId('YOUR_GOOGLE_CLIENT_ID');
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
$client->setRedirectUri('http://localhost/TP3%20(Projet)/HTML/google_callback.php');
$client->addScope("email");
$client->addScope("profile");

// Create the Google Sign-In URL
$authUrl = $client->createAuthUrl();*/

// Rest of your existing login.php code...

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginEmail = (string)$_POST['loginEmail'];
    $loginPassword = (string)$_POST['loginPassword'];

    // Validate inputs
    if (empty($loginEmail) || empty($loginPassword)) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit;
    }

    if (!filter_var($loginEmail, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid login credentials..'); window.history.back();</script>";
        exit;
    }    

    // Prepare the SQL statement to find the user
    $stmt = $conn->prepare("SELECT * FROM client WHERE email = :email");
    
    // Execute the statement with the provided email
    $stmt->execute(['email' => $loginEmail]);

    // Fetch the user
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Invalid login credentials.";
        exit;
    }

    $hashed_password = (string)$user['password'];

    if (password_verify($loginPassword, $hashed_password)) {
        // Password is correct
        session_start();

        // Generate CSRF token if not set
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a secure random token
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        session_set_cookie_params([
            'secure' => true, // Ensures the cookie is sent over HTTPS only
            'httponly' => true, // Prevents JavaScript from accessing the cookie
            'samesite' => 'Strict', // Protects against CSRF
        ]);        

        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_email"] = $user["email"]; // You may want to store the email too

        // Redirect to the homepage or another page
        header("Location: logged_in.php");
        exit;

    } else {
        // Invalid credentials
        echo "Invalid login credentials.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../JS/script.js"></script>
</head>
<body>
    <div class="container mt-5" name="login">
        <h2 class="text-center">Login</h2>
            <form method="POST" class="needs-validation" novalidate>

                <!-- CSRF Token Field -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <!-- Email Field -->
                <div class="mb-3">
                    <label for="loginEmail" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="loginEmail" 
                        name="loginEmail"  
                        class="form-control" 
                        required 
                        placeholder="Enter your email"
                    >
                    <div class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>
                
                <!-- Password Field -->
                <div class="mb-3">
                    <label for="loginPassword" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="loginPassword"  
                        name="loginPassword" 
                        class="form-control" 
                        required 
                        placeholder="Enter your password"
                    >
                    <div class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>
                <div class="display-6">
                    <button type="submit" class="btn btn-primary">Log in</button>
                    <button type="button" onclick="goHome()" class="btn btn-outline-primary">Go Back to Home</button>
                </div>
                <div class="mt-3 text-center">
                    <p>Or sign in with:</p>
                    <a href="<?php echo $client->createAuthUrl(); ?>" class="btn btn-danger">
                    <i class="fab fa-google"></i> Sign in with Google
                    </a>
                </div>
            </form>
    </div>
</body>
</html>

<?php

include("../PHP/connect.php");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginEmail = (string)$_POST['loginEmail'];
    $loginPassword = (string)$_POST['loginPassword'];

    // Validate inputs
    if (empty($loginEmail) || empty($loginPassword)) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit;
    }

    // Prepare the SQL statement to find the user
    $stmt = $conn->prepare("SELECT * FROM client WHERE email = :email");
    
    // Execute the statement with the provided email
    $stmt->execute(['email' => $loginEmail]);

    // Fetch the user
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $hashed_password = (string)$user['password'];

    if (password_verify($loginPassword, $hashed_password)) {
        // Password is correct
        session_start();

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_email"] = $user["email"]; // You may want to store the email too

        // Redirect to the homepage or another page
        header("Location: logged_in.php");
    } else {
        // Invalid credentials
        echo "Invalid email or password.";
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
            </form>
    </div>
</body>
</html>

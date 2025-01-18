<?php

include("../PHP/connect.php");

session_start();

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a secure random token
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo "All fields are required.";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid login credentials.";
        exit();
    }

    if ($password !== $confirmPassword) {
        echo "Invalid login credentials.";
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM client WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "Invalid login credentials.";
        } else {
            // Insert data into the database
            $stmt = $conn->prepare("INSERT INTO client (name, email, password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);

            if ($stmt->execute()) {
                echo "Sign-up successful! Redirecting back to home page...";
                header("Location: ../HTML/index.php"); // Redirect to login page after successful signup
                exit();
            } else {
                echo "Error: Unable to register the user.";
                header("Location: ../HTML/signup.php");
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../JS/script.js"></script>
</head>
<body>
    <div class="container mt-5" id="signup">
        <h2 class="text-center">Sign Up</h2>
        <form method="POST" class="needs-validation" novalidate>

            <!-- CSRF Token Field -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <!-- Name Field -->
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control" 
                    required 
                    placeholder="Enter your name"
                >
                <div class="invalid-feedback">
                    Please enter your name.
                </div>
            </div>

            <!-- Email Field -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
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
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    minlength="8" 
                    required 
                    placeholder="Enter a password (min 8 characters)"
                >
                <div class="invalid-feedback">
                    Password must be at least 8 characters long.
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirmPassword" 
                    name="confirmPassword" 
                    class="form-control" 
                    required 
                    placeholder="Confirm your password"
                >
                <div class="invalid-feedback">
                    Passwords do not match.
                </div>
            </div>
            <div class="display-6">
                <button type="submit" class="btn btn-primary">Sign Up</button>
                <button type="button" onclick="goHome()" class="btn btn-outline-primary">Go Back to Home</button>
            </div>
        </form>
    </div>
</body>
</html>

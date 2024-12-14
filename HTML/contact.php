<?php

include("../PHP/connect.php");

// Process the form if it is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate Name
    if (empty($_POST["contactName"])) {
        $nameErr = "Name is required.";
    } else {
        $name = sanitize_input($_POST["contactName"]);
        // Check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $nameErr = "Only letters and white space allowed.";
        }
    }

    // Validate Email
    if (empty($_POST["contactEmail"])) {
        $emailErr = "Email is required.";
    } else {
        $email = sanitize_input($_POST["contactEmail"]);
        // Check if email format is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format.";
        }
    }

    // Validate Message
    if (empty($_POST["contactMessage"])) {
        $messageErr = "Message is required.";
    } else {
        $message = sanitize_input($_POST["contactMessage"]);
    }

    // If there are no validation errors, insert the data into the database
    if (empty($nameErr) && empty($emailErr) && empty($messageErr)) {
        // Prepare the SQL insert statement
        $stmt = $conn->prepare("INSERT INTO contact (name, email, message) VALUES (:name, :email, :message)");

        // Bind parameters to the prepared statement
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':message', $message);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Your message has been sent successfully!";
            header("Location: contact.php");
            exit;
        } else {
            echo "There was an error submitting your message.";
            header("Location: ../HTML/contact.php");
            exit;
        }
    }
}

// Function to sanitize user input
function sanitize_input($data): string {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="../JS/script.js"></script>
</head>
<body>
    <div class="container mt-5" name="contact">
        <h2 class="text-center">Contact Us</h2>
        <p class="text-center">We would love to hear from you! Please fill out the form below to get in touch.</p>

        <!-- Contact Form -->
        <form method="POST" class="needs-validation" novalidate>
            <!-- Name Field -->
            <div class="mb-3">
                <label for="contactName" class="form-label">Your Name</label>
                <input type="text" id="contactName" name="contactName" class="form-control" required>
                <div class="invalid-feedback">
                    Please enter your name.
                </div>
            </div>

            <!-- Email Field -->
            <div class="mb-3">
                <label for="contactEmail" class="form-label">Your Email</label>
                <input type="email" id="contactEmail" name="contactEmail" class="form-control" required>
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>

            <!-- Message Field -->
            <div class="mb-3">
                <label for="contactMessage" class="form-label">Your Message</label>
                <textarea id="contactMessage" name="contactMessage" class="form-control" rows="5" required></textarea>
                <div class="invalid-feedback">
                    Please enter your message.
                </div>
            </div>
            <div class="display-6">
                <button type="submit" class="btn btn-primary">Send</button>
                <button type="button" onclick="goHome()" class="btn btn-outline-primary">Go Back to Home</button>
            </div>
        </form>
    </div>
</body>
</html>

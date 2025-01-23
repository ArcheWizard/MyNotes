<?php
include("../PHP/connect.php");
require_once '../../Dependencies/google-api-php-client/src/Google/autoload.php';

session_start();

$client = new Google_Client();
$client->setClientId('YOUR_GOOGLE_CLIENT_ID');
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
$client->setRedirectUri('http://localhost/TP3%20(Projet)/HTML/google_callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    // Get user profile
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $email = $google_account_info->email;
    $name = $google_account_info->name;

    // Check if user exists in database
    $stmt = $conn->prepare("SELECT * FROM client WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User doesn't exist, create a new account
        $stmt = $conn->prepare("INSERT INTO client (name, email, password) VALUES (:name, :email, :password)");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT) // Generate a random password
        ]);
    }

    // Log the user in
    $_SESSION["user_name"] = $name;
    $_SESSION["user_email"] = $email;

    // Generate CSRF token if not set
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    session_set_cookie_params([
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    // Redirect to the logged-in page
    header("Location: logged_in.php");
    exit;
} else {
    echo "Authentication failed.";
}
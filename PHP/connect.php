<?php
// Database configuration
$host = "localhost"; // Hostname of your MySQL Workbench server
$dbname = "mywebsite"; // Schema name
$username = "root"; // MySQL username (default is usually root)
$password = ""; // MySQL password (leave empty if not set)

try {
    // Create a new PDO instance
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
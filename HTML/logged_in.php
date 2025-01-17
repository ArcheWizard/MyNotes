<?php
// Start session for authentication
session_start();

include("../PHP/connect.php");

// Check if the user is logged in
if (!isset($_SESSION["user_email"])) {
    header("Location: login.php");
    exit;
}

// Get logged-in user's email
$user_email = $_SESSION["user_email"];

// Fetch notes for display (add this block here)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch-notes') {
    fetchNotes($conn, $user_email);
    exit; // Stop further execution
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'create') {
            $text = urldecode($_POST['text']);
            $sql = "INSERT INTO note (email, text) VALUES ('$user_email', '$text')";
            $conn->query($sql);
        } elseif ($action === 'edit') {
            $id = $_POST['id'];
            $text = urldecode($_POST['text']);
            $sql = "UPDATE note SET text='$text' WHERE id=$id AND email='$user_email'";
            $conn->query($sql);
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            $sql = "DELETE FROM note WHERE id=$id AND email='$user_email'";
            $conn->query($sql);
        }
    }
    exit;
}

// Fetch notes for display
function fetchNotes($conn, $user_email) {
    $stmt = $conn->prepare("SELECT * FROM note WHERE email = :email");
    $stmt->bindParam(':email', $user_email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Output the note content as raw HTML for rendering
            echo '<div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="note-content">' . $row['text'] . '</div> <!-- Display raw HTML -->
                        <button class="btn btn-primary btn-sm" onclick="popup(`' . htmlspecialchars($row['text'], ENT_QUOTES) . '`, ' . $row['id'] . ')">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteNote(' . $row['id'] . ')">Delete</button>
                    </div>
                  </div>';
        }
    } else {
        echo '<p class="text-muted">No notes to display. Add a new note!</p>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Notes</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <script src="../JS/note.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand fw-bold" href="logged_in.php">My Notes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="btn btn-outline-light mx-2 link-button" href="../PHP/disconnect.php">Log Out</a>
        </div>
    </nav>
    <section id="notesSection" class="notes-section py-5">
        <div class="container text-center">
            <h2 class="h4 mb-4">Welcome <?php echo $_SESSION['user_name']; ?></h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Add Note Button -->
                    <div class="card shadow-sm mb-3" onclick="popup()">
                        <div class="card-header">
                            <h5 class="card-title">Add Note</h5>
                        </div>
                    </div>
                    <!-- Notes List -->
                    <div id="notelist">
                        <!-- Notes will be dynamically added here -->
                        <?php fetchNotes($conn, $user_email); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer class="text-center py-3 bg-light mt-auto">
        <p class="mb-0 text-muted">&copy; 2024 My Notes</p>
    </footer>
</body>
</html>

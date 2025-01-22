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
            // Handle 'create' action
            $noteTitle = $_POST['title'];
            $text = urldecode($_POST['text']);

            // Use named placeholders for prepared statements
            $sql = $conn->prepare("INSERT INTO note (email, title, text) VALUES (:email, :title, :text)");
            $sql->execute([
                ':email' => $user_email,
                ':title' => $noteTitle,
                ':text' => $text
            ]);
        } elseif ($action === 'edit') {
            // Handle 'edit' action
            $id = $_POST['id'];
            $noteTitle = $_POST['title'];
            $text = urldecode($_POST['text']);

            // Use named placeholders for prepared statements
            $sql = $conn->prepare("UPDATE note SET title = :title, text = :text WHERE id = :id AND email = :email");
            $sql->execute([
                ':title' => $noteTitle,
                ':text' => $text,
                ':id' => $id,
                ':email' => $user_email
            ]);
        } elseif ($action === 'delete') {
            // Handle 'delete' action
            $id = $_POST['id'];

            // Use named placeholders for prepared statements
            $sql = $conn->prepare("DELETE FROM note WHERE id = :id AND email = :email");
            $sql->execute([
                ':id' => $id,
                ':email' => $user_email
            ]);
        } elseif ($action === 'updateOrder'){
            $orderData = json_decode($_POST['order'], true);
                foreach ($orderData as $item) {
                    $stmt = $conn->prepare("UPDATE note SET display_order = :order WHERE id = :id AND email = :email");
                    $stmt->execute([
                        ':order' => $item['order'],
                        ':id' => $item['id'],
                        ':email' => $user_email
                    ]);
                }

        } elseif ($action === 'upload'){
            $noteId = $_POST['noteId'];
                $uploadDir = '../Uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $files = [];
                foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                    $fileName = $_FILES['files']['name'][$key];
                    $fileSize = $_FILES['files']['size'][$key];
                    $fileType = $_FILES['files']['type'][$key];
                    $filePath = $uploadDir . uniqid() . '_' . $fileName;

                    if (move_uploaded_file($tmp_name, $filePath)) {
                        $stmt = $conn->prepare("INSERT INTO attachments (note_id, file_name, file_path, file_type, file_size) VALUES (:note_id, :file_name, :file_path, :file_type, :file_size)");
                        $stmt->execute([
                            ':note_id' => $noteId,
                            ':file_name' => $fileName,
                            ':file_path' => $filePath,
                            ':file_type' => $fileType,
                            ':file_size' => $fileSize
                        ]);
                        
                        $files[] = [
                            'id' => $conn->lastInsertId(),
                            'name' => $fileName
                        ];
                    }
                }
                echo json_encode($files);
            
        }
    }
    exit;
}


// Fetch notes for display
function fetchNotes($conn, $user_email) {
    $allowedSortOrders = ['asc', 'desc'];
    $sortOrder = isset($_GET['sort']) && in_array(strtolower($_GET['sort']), $allowedSortOrders) ? strtoupper($_GET['sort']) : 'ASC';
    $filter = isset($_GET['filter']) ? '%' . $_GET['filter'] . '%' : '%';

    $stmt = $conn->prepare("SELECT * FROM note WHERE email = :email AND title LIKE :filter ORDER BY title $sortOrder");
    $stmt->bindParam(':email', $user_email, PDO::PARAM_STR);
    $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Fetch attachments for this note
                $attachments = fetchAttachments($conn, $row['id']);
                $attachmentsList = '';
                
                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        $attachmentsList .= sprintf(
                            '<div class="file-item">
                                <span>%s</span>
                                <button class="btn btn-sm btn-danger" onclick="deleteFile(%d, %d)">Delete</button>
                            </div>',
                            htmlspecialchars($attachment['file_name']),
                            $attachment['id'],
                            $row['id']
                        );
                    }
                }

                echo '<div class="card shadow-sm mb-3" data-note-id="' . $row['id'] . '">
                    <div class="card-body">
                        <div class="drag-handle">☰</div>
                        <h3>' . htmlspecialchars($row['title']) . '</h3>
                        <div class="note-content">' . $row['text'] . '</div>
                        <button class="btn btn-primary btn-sm" onclick="popup(`' . htmlspecialchars($row['text'], ENT_QUOTES) . '`, ' . $row['id'] . ', `' . htmlspecialchars($row['title'], ENT_QUOTES) . '`)">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteNote(' . $row['id'] . ')">Delete</button>
                    </div>
                </div>';
            }
        } else {
            echo '<p class="text-muted">No notes to display. Add a new note!</p>';
        }
    } else {
        error_log("Query failed: " . print_r($stmt->errorInfo(), true));
    }
}

// Add this new function to fetch attachments
function fetchAttachments($conn, $note_id) {
    $stmt = $conn->prepare("SELECT id, file_name, file_path FROM attachments WHERE note_id = :note_id");
    $stmt->execute([':note_id' => $note_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add this new endpoint to handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteFile') {
    $fileId = $_POST['fileId'];
    
    // First get the file path
    $stmt = $conn->prepare("SELECT file_path FROM attachments WHERE id = :id");
    $stmt->execute([':id' => $fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($file) {
        // Delete the physical file
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        
        // Delete the database record
        $stmt = $conn->prepare("DELETE FROM attachments WHERE id = :id");
        $stmt->execute([':id' => $fileId]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'File not found']);
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Notes</title>
    <script src="../JS/note.js"></script>
    <script src="../JS/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">My Notes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="btn btn-outline-light mx-2 link-button" href="../PHP/disconnect.php">Log Out</a></li>
                    <li class="nav-item"><a class="btn btn-outline-light mx-2 link-button" href="contact.php">Contact Us</a></li>
                    <li class="nav-item theme-switch-wrapper">
                        <label class="theme-switch" for="checkbox">
                            <input type="checkbox" id="checkbox" />
                            <div class="slider"></div>
                        </label>
                    </li>
                </ul>
            </div>
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
                    <div id="options" class="card shadow-sm mb-3">
                        <div class="card-header">
                            <select id="sortTitle" class="form-control" onchange="sortNotes()">
                                <option value="ASC">Sort by Title (A-Z)</option>
                                <option value="DESC">Sort by Title (Z-A)</option>
                            </select>
                            <input type="text" id="titleFilter" class="form-control mt-2" placeholder="Filter by title" onkeyup="filterNotes()">
                        </div>
                    </div>
                    <!-- Notes List -->
                    <div id="notelist" class="sortable-notes">
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
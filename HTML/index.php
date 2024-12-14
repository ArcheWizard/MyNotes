<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes - Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.html">My Notes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="btn btn-outline-light mx-2 link-button" href="signup.php">Sign Up</a></li>
                    <li class="nav-item"><a class="btn btn-outline-light mx-2 link-button" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-outline-light mx-2 link-button" href="contact.php">Contact Us</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="header-section">
        <div class="container text-center py-5">
            <h1 class="fw-bold mb-3">Welcome to My Notes</h1>
            <p class="mb-4">Secure and user-friendly note-taking made simple.</p>
            <a href="signup.php" class="btn btn-primary link-button">Get Started</a>
        </div>
    </header>

    <section class="features-section py-5">
        <div class="container text-center">
            <h2 class="h4 mb-3">Features</h2>
            <p class="text-muted mb-4">Sign up or log in to create and organize your notes easily.</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-box shadow-sm py-4 px-3 mb-3">
                        <h5>Create Notes</h5>
                        <p class="text-muted">Write down all your thoughts and ideas securely.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box shadow-sm py-4 px-3 mb-3">
                        <h5>Organize Notes</h5>
                        <p class="text-muted">Categorize and manage your notes with ease.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box shadow-sm py-4 px-3 mb-3">
                        <h5>Access Anywhere</h5>
                        <p class="text-muted">View and edit your notes from any device.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="notesSection" class="notes-section py-5">
        <div class="container text-center">
            <h2 class="h4 mb-4">Your Notes</h2>
            <div class="row justify-content-center">
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Note Title</h5>
                            <p class="card-text text-muted">This is a short description of your note...</p>
                            <a href="#" class="btn btn-primary btn-sm link-button">View Note</a>
                        </div>
                    </div>
                </div>
                <!-- Add more note cards here dynamically -->
            </div>
        </div>
    </section>

    <footer class="text-center py-3 bg-light mt-auto">
        <p class="mb-0 text-muted">&copy; 2024 My Notes</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../JS/script.js"></script>
</body>
</html>

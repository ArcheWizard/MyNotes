<?php
session_start();
include("../PHP/connect.php");

// Check if admin is logged in (you'll need to implement admin authentication)
if (!isset($_SESSION["admin_email"])) {
    header("Location: login.php");
    exit;
}

// Analytics Queries
function getUserAnalytics($conn) {
    $analytics = [];

    // Total Users
    $stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM client");
    $stmt->execute();
    $analytics['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Notes Statistics
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total_notes, 
        COUNT(DISTINCT email) as users_with_notes,
        AVG(notes_count) as avg_notes_per_user
    FROM (
        SELECT email, COUNT(*) as notes_count 
        FROM note 
        GROUP BY email
    ) user_notes");
    $stmt->execute();
    $notesStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $analytics['total_notes'] = $notesStats['total_notes'];
    $analytics['users_with_notes'] = $notesStats['users_with_notes'];
    $analytics['avg_notes_per_user'] = round($notesStats['avg_notes_per_user'], 2);

    // Recent Activity
    $stmt = $conn->prepare("SELECT 
        email, 
        MAX(created_at) as last_activity, 
        COUNT(*) as recent_notes 
    FROM note 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
    GROUP BY email 
    ORDER BY recent_notes DESC 
    LIMIT 10");
    $stmt->execute();
    $analytics['recent_activity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // File Attachments
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total_attachments, 
        SUM(file_size)/1024/1024 as total_storage_mb 
    FROM attachments");
    $stmt->execute();
    $fileStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $analytics['total_attachments'] = $fileStats['total_attachments'];
    $analytics['total_storage_mb'] = round($fileStats['total_storage_mb'], 2);

    return $analytics;
}

$analytics = getUserAnalytics($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Admin Dashboard</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="../PHP/disconnect.php" class="btn btn-outline-light">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">User Statistics</div>
                    <div class="card-body">
                        <p>Total Users: <?php echo $analytics['total_users']; ?></p>
                        <p>Users with Notes: <?php echo $analytics['users_with_notes']; ?></p>
                        <p>Avg Notes per User: <?php echo $analytics['avg_notes_per_user']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">Notes Overview</div>
                    <div class="card-body">
                        <p>Total Notes: <?php echo $analytics['total_notes']; ?></p>
                        <p>Total Attachments: <?php echo $analytics['total_attachments']; ?></p>
                        <p>Storage Used: <?php echo $analytics['total_storage_mb']; ?> MB</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">Recent Activity</div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php foreach($analytics['recent_activity'] as $activity): ?>
                                <li>
                                    <?php echo htmlspecialchars($activity['email']); ?>: 
                                    <?php echo $activity['recent_notes']; ?> notes 
                                    (Last active: <?php echo $activity['last_activity']; ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">User Growth</div>
                    <div class="card-body">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Notes Distribution</div>
                    <div class="card-body">
                        <canvas id="notesDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'User Growth',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            }
        });

        // Notes Distribution Chart
        const notesDistributionCtx = document.getElementById('notesDistributionChart').getContext('2d');
        new Chart(notesDistributionCtx, {
            type: 'pie',
            data: {
                labels: ['0-5 Notes', '6-10 Notes', '11-20 Notes', '20+ Notes'],
                datasets: [{
                    data: [300, 50, 100, 75],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)'
                    ]
                }]
            }
        });
    </script>
</body>
</html>
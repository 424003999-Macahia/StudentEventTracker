<?php
session_start();
// Bring in the PDO database connection variable ($pdo)
require_once '../db.php';

// Security Guard: If a user is not logged in or is NOT a student, kick them back to login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// SQL JOIN: Links registrations to the events table for the logged-in student
$query = "SELECT e.*, r.registered_at 
          FROM registrations r
          JOIN events e ON r.event_id = e.id
          WHERE r.user_id = ?
          ORDER BY e.event_date ASC";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$my_events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | My Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold me-4" href="dashboard.php"> Student Campus Events</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNavbarMenu" aria-controls="studentNavbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="studentNavbarMenu">
                <div class="navbar-nav me-auto d-flex gap-2 my-2 my-lg-0">
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm px-3 fw-medium">🔍 All Events</a>
                    <a href="my-events.php" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm">📅 My Registered Events</a>
                    <a href="my-account.php" class="btn btn-outline-light btn-sm px-3 fw-medium">👤 My Account</a>
                </div>
                
                <div class="navbar-nav ms-auto align-items-lg-center">
                    <span class="navbar-text text-white-50 me-lg-3 my-2 my-lg-0">
                        Logged in as: <strong class="text-white"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Student'); ?></strong>
                    </span>
                    <a href="../auth/logout.php" class="btn btn-sm btn-outline-danger px-3 w-auto">Log Out</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="p-5 bg-white rounded shadow-sm border">
            <h1 class="fw-bold text-success mb-2">My Registered Schedule</h1>
            <p class="text-muted mb-4">Here are the upcoming activities you are confirmed to attend.</p>
            <hr class="my-4">

            <?php if (empty($my_events)): ?>
                <div class="text-center py-5 bg-light rounded border border-dashed">
                    <p class="text-muted mb-0">You haven't signed up for any events yet. Go to the dashboard to register!</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($my_events as $row): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-start border-success border-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($row['title']); ?></h5>
                                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($row['description']); ?></p>
                                    <div class="small mb-1"><strong>📍 Location:</strong> <?php echo htmlspecialchars($row['location']); ?></div>
                                    <div class="small"><strong>📅 Time:</strong> <?php echo date('M d, Y @ h:i A', strtotime($row['event_date'])); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
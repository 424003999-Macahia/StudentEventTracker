<?php
session_start();
require_once '../db.php';

// Security Guard: Student only!
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student user info
$user_stmt = $pdo->prepare("SELECT fullname, email, created_at FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user_info = $user_stmt->fetch();

// Calculate total metrics
$stats_stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = ?");
$stats_stmt->execute([$user_id]);
$total_registrations = $stats_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | My Account</title>
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
                    <a href="my-events.php" class="btn btn-outline-light btn-sm px-3 fw-medium">📅 My Registered Events</a>
                    <a href="my-account.php" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm">👤 My Account</a>
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

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0 rounded-3">
                    <div class="card-body p-5 text-center">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                            <?php echo strtoupper(substr($user_info['fullname'] ?? 'S', 0, 1)); ?>
                        </div>
                        
                        <h3 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($user_info['fullname'] ?? 'Student'); ?></h3>
                        <p class="text-muted mb-4">Student Hub Member</p>
                        
                        <hr class="my-4">
                        
                        <div class="text-start mb-4">
                            <div class="mb-3">
                                <label class="small fw-bold text-secondary text-uppercase d-block mb-1">Email Address / User Handle</label>
                                <span class="fs-6 text-dark fw-medium"><?php echo htmlspecialchars($user_info['email'] ?? 'Not Specified'); ?></span>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-secondary text-uppercase d-block mb-1">Total Registrations Completed</label>
                                <span class="badge bg-primary fs-6 py-2 px-3"><?php echo $total_registrations; ?> Events Reserved</span>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-secondary text-uppercase d-block mb-1">Account Created On</label>
                                <small class="text-muted fs-6"><?php echo isset($user_info['created_at']) ? date('F d, Y', strtotime($user_info['created_at'])) : 'N/A'; ?></small>
                            </div>
                        </div>

                        <a href="dashboard.php" class="btn btn-secondary w-100 fw-medium py-2">Return to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
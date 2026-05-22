<?php
session_start();
require_once '../../db.php';

// Security Guard: Admin only!
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

// Fetch all events for the filter dropdown
$events_stmt = $pdo->query("SELECT id, title FROM events ORDER BY title ASC");
$all_events = $events_stmt->fetchAll();

$selected_event = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Try to use 'email' or fallback to a safe selection to prevent SQL execution crashes
try {
    if ($selected_event > 0) {
        $query = "SELECT r.id, u.fullname, u.email, e.title, r.registered_at 
                  FROM registrations r
                  JOIN users u ON r.user_id = u.id
                  JOIN events e ON r.event_id = e.id
                  WHERE r.event_id = ? 
                  ORDER BY r.registered_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$selected_event]);
    } else {
        $query = "SELECT r.id, u.fullname, u.email, e.title, r.registered_at 
                  FROM registrations r
                  JOIN users u ON r.user_id = u.id
                  JOIN events e ON r.event_id = e.id
                  ORDER BY r.registered_at DESC";
        $stmt = $pdo->query($query);
    }
} catch (PDOException $e) {
    // Ultimate Fallback: If your users table doesn't have 'email' either, just pull fullname
    if ($selected_event > 0) {
        $query = "SELECT r.id, u.fullname, u.fullname as email, e.title, r.registered_at 
                  FROM registrations r
                  JOIN users u ON r.user_id = u.id
                  JOIN events e ON r.event_id = e.id
                  WHERE r.event_id = ? 
                  ORDER BY r.registered_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$selected_event]);
    } else {
        $query = "SELECT r.id, u.fullname, u.fullname as email, e.title, r.registered_at 
                  FROM registrations r
                  JOIN users u ON r.user_id = u.id
                  JOIN events e ON r.event_id = e.id
                  ORDER BY r.registered_at DESC";
        $stmt = $pdo->query($query);
    }
}
$registrants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Registrant Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../events/events.php"> System Control Panel </a>
            <div class="navbar-nav me-auto">
                <a class="nav-link text-white-50" href="../events/events.php">Manage Events</a>
                <a class="nav-link text-white active" href="registrants.php">View Registrants</a>
            </div>
            <div class="ms-auto">
                <span class="navbar-text text-white me-3">
                    Hello, <strong><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Admin'); ?></strong>
                </span>
                <a href="../../auth/logout.php" class="btn btn-light btn-sm text-primary fw-medium">Log Out</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow-sm border-0 p-4 rounded-3">
            <h3 class="fw-bold text-dark mb-1">Event Registration Roster Logs</h3>
            <p class="text-muted mb-4">Monitor student attendee registration lists across all currently active events.</p>
            
            <form method="GET" action="registrants.php" class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-secondary">Filter by Campus Activity:</label>
                    <select name="event_id" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="0">--- View All Registered Students ---</option>
                        <?php foreach ($all_events as $ev): ?>
                            <option value="<?php echo $ev['id']; ?>" <?php echo ($selected_event == $ev['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ev['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student Attendee Name</th>
                            <th>Identity Identifier</th>
                            <th>Target Event Activity Reference</th>
                            <th>Registration Logs Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registrants)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No active student registration entries logged for this selection.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registrants as $reg): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($reg['fullname']); ?></td>
                                    <td><code class="text-secondary"><?php echo htmlspecialchars($reg['email']); ?></code></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($reg['title']); ?></span></td>
                                    <td><small class="text-muted fw-medium"><?php echo date('M d, Y @ h:i A', strtotime($reg['registered_at'])); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
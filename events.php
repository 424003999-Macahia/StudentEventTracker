<?php
session_start();
require_once '../../db.php';

// Security Guard: Admin only!
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

$message = "";
$message_class = "";

// --- HANDLE DELETE REQUEST ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $pdo->beginTransaction();
        
        // 1. Delete linked student registrations first to prevent foreign key errors
        $clean_regs = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
        $clean_regs->execute([$delete_id]);

        // 2. Delete the actual event
        $delete_stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $delete_stmt->execute([$delete_id]);

        $pdo->commit();
        header("Location: events.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "❌ Error deleting event: " . $e->getMessage();
        $message_class = "alert-danger";
    }
}

// --- HANDLE CREATE REQUEST ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_event'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']); 
    $total_slots = intval($_POST['total_slots']);

    if (!empty($title) && !empty($description) && !empty($event_date) && !empty($location) && $total_slots > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, total_slots, available_slots) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $description, $event_date, $location, $total_slots, $total_slots])) {
                $message = "🎉 Event created successfully!";
                $message_class = "alert-success";
            }
        } catch (PDOException $e) {
            $message = "❌ Database error: " . $e->getMessage();
            $message_class = "alert-danger";
        }
    } else {
        $message = "⚠️ Please fill out all fields correctly.";
        $message_class = "alert-warning";
    }
}

// Fetch all events for the table layout view
$events_stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
$events = $events_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Event Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="events.php"> System Control Panel </a>
            <div class="navbar-nav me-auto">
                <a class="nav-link text-white active" href="events.php">Manage Events</a>
                <a class="nav-link text-white-50" href="../registrants/registrants.php">View Registrants</a>
            </div>
            <div class="ms-auto">
                <span class="navbar-text text-white me-3">
                    Hello, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong>
                </span>
                <a href="../../auth/logout.php" class="btn btn-light btn-sm text-primary fw-medium">Log Out</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">
                        <h4 class="fw-bold text-dark mb-3">Create New Event</h4>
                        <form action="events.php" method="POST">
                            <input type="hidden" name="create_event" value="1">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Event Title</label>
                                <input type="text" name="title" class="form-control" required placeholder="e.g. IT Seminar 2026">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Description</label>
                                <textarea name="description" class="form-control" rows="3" required placeholder="Describe the campus event..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Event Date & Time</label>
                                <input type="datetime-local" name="event_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Venue / Location</label>
                                <input type="text" name="location" class="form-control" required placeholder="e.g. Campus Gym, Room 402">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-secondary">Available Slots</label>
                                <input type="number" name="total_slots" class="form-control" min="1" required placeholder="e.g. 100">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Publish Event</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">
                        <h4 class="fw-bold text-dark mb-3">Active Campus Events</h4>
                        
                        <?php if (empty($events)): ?>
                            <div class="text-center py-5 bg-light rounded border border-dashed">
                                <p class="text-muted mb-0">No active events yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Event Details</th>
                                            <th>Location</th>
                                            <th>Scheduled Date</th>
                                            <th class="text-center">Available / Total</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $row): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['title']); ?></div>
                                                    <small class="text-muted d-inline-block text-truncate" style="max-width: 180px;">
                                                        <?php echo htmlspecialchars($row['description']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($row['location']); ?></span>
                                                </td>
                                                <td><small class="fw-medium"><?php echo date('M d, Y @ h:i A', strtotime($row['event_date'])); ?></small></td>
                                                <td class="text-center">
                                                    <span class="fw-bold text-dark"><?php echo $row['available_slots']; ?></span>
                                                    <span class="text-muted small">/ <?php echo $row['total_slots']; ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <a href="edit-event.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning fw-medium">Edit</a>
                                                        <a href="events.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger fw-medium" onclick="return confirmDelete('<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>');">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(eventTitle) {
        return confirm("⚠️ Are you absolutely sure you want to delete '" + eventTitle + "'?\nThis will permanently wipe out all student registrations for it!");
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
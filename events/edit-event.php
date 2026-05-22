<?php
session_start();
require_once '../../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

$message = "";
$message_class = "";
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the existing event data
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    die("Event not found.");
}

// Handle Form Update Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);
    $total_slots = intval($_POST['total_slots']);

    // Manage standard seats slot offset math modifications safely
    $slots_diff = $total_slots - $event['total_slots'];
    $new_available = $event['available_slots'] + $slots_diff;

    if (!empty($title) && !empty($description) && !empty($event_date) && !empty($location) && $total_slots > 0) {
        if ($new_available < 0) {
            $message = "⚠️ Cannot lower slot capacity below active registration counts.";
            $message_class = "alert-warning";
        } else {
            try {
                $update_stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, total_slots = ?, available_slots = ? WHERE id = ?");
                if ($update_stmt->execute([$title, $description, $event_date, $location, $total_slots, $new_available, $event_id])) {
                    header("Location: events.php");
                    exit();
                }
            } catch (PDOException $e) {
                $message = "❌ Error: " . $e->getMessage();
                $message_class = "alert-danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 600px;">
        <?php if(!empty($message)): ?>
            <div class="alert <?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <div class="card shadow-sm border-0 rounded-3 p-4">
            <h4 class="fw-bold mb-3 text-dark">Modify Event Details</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Event Title</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Description</label>
                    <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Date & Time</label>
                    <input type="datetime-local" name="event_date" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Venue / Location</label>
                    <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($event['location']); ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Total Seat Capacity Slots</label>
                    <input type="number" name="total_slots" class="form-control" value="<?php echo $event['total_slots']; ?>" min="1" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Changes</button>
                    <a href="events.php" class="btn btn-light w-100 fw-medium border">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
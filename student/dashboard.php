<?php
session_start();
// Bring in the PDO database connection variable ($pdo)
require_once '../db.php';

// Security Guard: If a user is not logged in or is NOT a student, kick them back to login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Captured during your user sign-in session
$message = "";
$message_class = "";

// --- HANDLE EVENT REGISTRATION REQUEST ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_event_id'])) {
    $event_id = intval($_POST['register_event_id']);

    try {
        $pdo->beginTransaction();

        // 1. Check if the event still has slots available
        $event_stmt = $pdo->prepare("SELECT total_slots, available_slots FROM events WHERE id = ? FOR UPDATE");
        $event_stmt->execute([$event_id]);
        $event = $event_stmt->fetch();

        // 2. Check if the student is already registered
        $check_stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
        $check_stmt->execute([$user_id, $event_id]);
        $already_registered = $check_stmt->fetch();

        if (!$event) {
            $message = "❌ Event not found.";
            $message_class = "alert-danger";
            $pdo->rollBack();
        } elseif ($already_registered) {
            $message = "⚠️ You are already registered for this event!";
            $message_class = "alert-warning";
            $pdo->rollBack();
        } elseif ($event['available_slots'] <= 0) {
            $message = "🚫 Sorry, this event is already sold out!";
            $message_class = "alert-danger";
            $pdo->rollBack();
        } else {
            // 3. Process registration insert mapping row
            $insert_stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
            $insert_stmt->execute([$user_id, $event_id]);

            // 4. Deduct an available seat slot safely
            $update_stmt = $pdo->prepare("UPDATE events SET available_slots = available_slots - 1 WHERE id = ?");
            $update_stmt->execute([$event_id]);

            $pdo->commit();
            $message = "🎉 Registration successful! See you at the venue.";
            $message_class = "alert-success";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "❌ Error processing registration: " . $e->getMessage();
        $message_class = "alert-danger";
    }
}

// --- FETCH CAMPUS EVENTS DYNAMICALLY ---
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.user_id = ?) as joined 
          FROM events e ORDER BY e.event_date ASC";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | Dashboard</title>
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
                    <a href="dashboard.php" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm">🔍 All Events</a>
                    
                    <a href="my-events.php" class="btn btn-outline-light btn-sm px-3 fw-medium">📅 My Registered Events</a>
                    
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
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $message_class; ?> alert-dismissible fade show shadow-sm" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="p-5 bg-white rounded shadow-sm border">
            <h1 class="display-5 fw-bold text-primary">Student Dashboard</h1>
            <p class="lead text-muted">Welcome to your campus activity hub. Here you can discover, register for, and manage school events.</p>
            <hr class="my-4">
            
            <h3 class="fw-bold text-dark mb-4">Upcoming Campus Events</h3>

            <?php if (empty($events)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card p-4 border-dashed text-center bg-light">
                            <h4 class="text-secondary mb-2">Upcoming Campus Events</h4>
                            <p class="text-muted mb-0">No upcoming events are posted at the moment. Check back soon!</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($events as $row): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-light">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold text-primary mb-2"><?php echo htmlspecialchars($row['title']); ?></h5>
                                    <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars($row['description']); ?></p>
                                    
                                    <hr class="text-black-50 my-2">
                                    
                                    <div class="mb-1 small"><strong>📍 Venue:</strong> <?php echo htmlspecialchars($row['location']); ?></div>
                                    <div class="mb-1 small"><strong>📅 Date:</strong> <?php echo date('M d, Y @ h:i A', strtotime($row['event_date'])); ?></div>
                                    <div class="mb-3 small"><strong>🎟️ Available Slots:</strong> 
                                        <span class="badge <?php echo ($row['available_slots'] > 0) ? 'bg-info' : 'bg-danger'; ?>">
                                            <?php echo $row['available_slots'] . ' / ' . $row['total_slots']; ?>
                                        </span>
                                    </div>

                                    <?php if ($row['joined'] > 0): ?>
                                        <button class="btn btn-secondary w-100 fw-bold" disabled>
                                            ✔️ Registered
                                        </button>
                                    <?php elseif ($row['available_slots'] <= 0): ?>
                                        <button class="btn btn-outline-danger w-100 fw-bold" disabled>
                                            Sold Out
                                        </button>
                                    <?php else: ?>
                                        <form action="dashboard.php" method="POST" class="mt-auto">
                                            <input type="hidden" name="register_event_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-primary w-100 fw-bold">Register Now</button>
                                        </form>
                                    <?php endif; ?>
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
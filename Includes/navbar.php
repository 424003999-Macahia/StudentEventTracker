<?php
// Ensure session is active before checking roles
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dynamically determine the base path so links work from both /admin and /student subfolders
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_subfolder = ($current_dir === 'admin' || $current_dir === 'student' || $current_dir === 'registrants' || $current_dir === 'events');
$prefix = $is_subfolder ? '../' : '';
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-2">
    <div class="container">
        <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2" href="<?php echo $prefix; ?><?php echo ($user_role === 'admin') ? 'admin/events/events.php' : 'student/dashboard.php'; ?>">
            🎉 Campus Event Hub
        </a>
        
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#centralNavbarMenu" aria-controls="centralNavbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="centralNavbarMenu">
            <div class="navbar-nav me-auto d-flex gap-2 my-3 my-lg-0">
                
                <?php if ($user_role === 'admin'): ?>
                    <a href="<?php echo $prefix; ?>admin/events/events.php" class="btn btn-sm px-3 fw-bold <?php echo ($current_page === 'events.php' || $current_page === 'edit-event.php') ? 'btn-primary text-white shadow' : 'btn-outline-light'; ?>">
                        🖥️ Manage Events
                    </a>
                    <a href="<?php echo $prefix; ?>admin/registrants/registrants.php" class="btn btn-sm px-3 fw-bold <?php echo ($current_page === 'registrants.php') ? 'btn-primary text-white shadow' : 'btn-outline-light'; ?>">
                        👥 View Registrants
                    </a>

                <?php else: ?>
                    <a href="<?php echo $prefix; ?>student/dashboard.php" class="btn btn-sm px-3 fw-bold <?php echo ($current_page === 'dashboard.php') ? 'btn-primary text-white shadow' : 'btn-outline-light'; ?>">
                        🔵 All Events
                    </a>
                    <a href="<?php echo $prefix; ?>student/my-events.php" class="btn btn-sm px-3 fw-bold <?php echo ($current_page === 'my-events.php') ? 'btn-primary text-white shadow' : 'btn-outline-light'; ?>">
                        📅 My Registered Events
                    </a>
                    <a href="<?php echo $prefix; ?>student/my-account.php" class="btn btn-sm px-3 fw-bold <?php echo ($current_page === 'my-account.php') ? 'btn-primary text-white shadow' : 'btn-outline-light'; ?>">
                        👤 My Account
                    </a>
                <?php endif; ?>

            </div>
            
            <div class="navbar-nav ms-auto align-items-lg-center">
                <span class="navbar-text text-white-50 me-lg-3 my-2 my-lg-0">
                    Logged in as: <strong class="text-white"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></strong> 
                    <span class="badge <?php echo ($user_role === 'admin') ? 'bg-danger' : 'bg-success'; ?> ms-1 text-uppercase" style="font-size: 0.7rem;">
                        <?php echo $user_role; ?>
                    </span>
                </span>
                <a href="<?php echo $prefix; ?>auth/logout.php" class="btn btn-sm btn-outline-danger px-3 w-auto fw-bold">Log Out</a>
            </div>
        </div>
    </div>
</nav>
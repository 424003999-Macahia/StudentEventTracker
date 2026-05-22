<?php
// 1. Include the database connection file
require_once '../db.php';

// Initialize alert messages
$message = "";
$message_class = "";

// 2. Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic server-side validation
    if (!empty($fullname) && !empty($email) && !empty($password)) {
        
        // Check if email already exists in the database
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->rowCount() > 0) {
            $message = "Email address is already registered!";
            $message_class = "alert-danger";
        } else {
            // 3. Securely hash the password (Bcrypt)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // 4. Insert user into database (Default role is 'student')
            $insert = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'student')");
            
            if ($insert->execute([$fullname, $email, $hashed_password])) {
                $message = "Registration successful! You can now log in.";
                $message_class = "alert-success";
            } else {
                $message = "Something went wrong. Please try again.";
                $message_class = "alert-danger";
            }
        }
    } else {
        $message = "Please fill in all fields.";
        $message_class = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Events | Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.5)), 
                              url('https://images.unsplash.com/photo-1541339907198-e08756defe63?q=80&w=1920');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
        }
        .registration-card {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(12.5px);
            -webkit-backdrop-filter: blur(12.5px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .form-control {
            background-color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
        }
        .btn-register {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #4f46e5 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card registration-card p-2 text-white">
                <div class="card-header border-0 text-center bg-transparent pt-4">
                    <h2 class="fw-bold tracking-tight text-white mb-1">Create Account</h2>
                    <p class="text-white-50 mb-0">Join the Student Event Portal</p>
                </div>
                <div class="card-body p-4">
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_class; ?> text-center" role="alert">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-white-50">Full Name</label>
                            <input type="text" name="fullname" class="form-control" required placeholder="John B. Doe">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-white-50">Email Address</label>
                            <input type="email" name="email" class="form-control" required placeholder="j.doe@school.edu.ph">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-white-50">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••••">
                        </div>
                        
                        <div class="mt-4 pt-2">
                            <button type="submit" class="btn btn-register w-100 btn-primary text-white">
                                Register Now
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4 mb-2">
                        <p class="text-white-50 small mb-0">
                            Already registered? <a href="login.php" class="text-white fw-medium text-decoration-none">Log In here</a>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
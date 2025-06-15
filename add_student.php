<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();
redirectIfNotRole('admin');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $division = trim($_POST['division']);
    $roll = intval($_POST['roll']);

    if (!$name || !$username || !$password || !$division || !$roll || !$email) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $message = "Username already exists. Choose another.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $message = "Email already exists. Use a different one.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role, division, roll) VALUES (?, ?, ?, ?, 'student', ?, ?)");
                $stmt->execute([$name, $username, $email, $passwordHash, $division, $roll]);

                if ($stmt->rowCount()) {
                    $message = "Student added successfully!";
                } else {
                    $message = "Failed to add student.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.5rem;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px;
        }
        .btn-primary {
            background-color: #4e73df;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .btn-primary:hover {
            background-color: #3a5bc7;
        }
        .message {
            margin-top: 20px;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        .password-wrapper {
            position: relative;
        }
        #togglePassword {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header text-center">
                <h2>Add New Student</h2>
            </div>
            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="Enter student's full name">
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="Enter username">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter student email">
                    </div>

                    <div class="mb-3 password-wrapper">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="division" class="form-label">Division</label>
                            <input type="text" class="form-control" id="division" name="division" required placeholder="e.g., A, B, C">
                        </div>
                        <div class="col-md-6">
                            <label for="roll" class="form-label">Roll Number</label>
                            <input type="number" class="form-control" id="roll" name="roll" required placeholder="Enter roll number">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Add Student</button>
                    </div>
                </form>

                <a href="index.php" class="back-link text-decoration-none mt-3">
                    <i class="fas fa-arrow-left"></i> Back to Admin Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordField = document.getElementById("password");
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    </script>
</body>
</html>

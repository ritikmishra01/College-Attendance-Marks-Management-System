<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();
redirectIfNotRole('admin');

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$name || !$username || !$email || !$password) {
        $message = "All fields are required.";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
        $message_type = 'error';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $message = "Username or email already exists. Choose another.";
            $message_type = 'error';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert teacher
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, 'teacher')");
            $stmt->execute([$name, $username, $email, $passwordHash]);

            if ($stmt->rowCount()) {
                $message = "Teacher added successfully!";
                $message_type = 'success';
                $name = $username = $email = '';
            } else {
                $message = "Failed to add teacher.";
                $message_type = 'error';
            }
        }
    }
}
?>
<?php
// [Keep your existing PHP code exactly the same]
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher | Academic Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --success-color: #4bb543;
            --error-color: #ff3333;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #2b2b2b;
            --text-light: #6c757d;
            --border-color: #e1e5eb;
            --border-radius: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles would go here */
        /* Main content area */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-color);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link i {
            margin-right: 8px;
        }

        .back-link:hover {
            color: var(--primary-dark);
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }

        .alert-success {
            background-color: rgba(75, 181, 67, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(75, 181, 67, 0.3);
        }

        .alert-error {
            background-color: rgba(255, 51, 51, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(255, 51, 51, 0.3);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: all 0.3s;
            background-color: #fcfcfc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            background-color: #fff;
        }

        .input-group {
            position: relative;
        }

        .input-group .form-control {
            padding-right: 40px;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 500;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
            color: var(--text-light);
        }

        .strength-meter {
            height: 4px;
            background: #eee;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-meter-fill {
            height: 100%;
            width: 0;
            transition: width 0.3s, background 0.3s;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }
            
            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar would go here -->
        
        <div class="main-content">
            <div class="card">
                <div class="page-header">
                    <h1 class="page-title">Add New Teacher</h1>
                    <a href="index.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'error' ?>">
                        <i class="fas <?= $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" 
                               placeholder="Enter teacher's full name" required>
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" 
                               placeholder="Choose a username" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" 
                               placeholder="teacher@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Create a password" required>
                            <i class="fas fa-eye input-icon" id="togglePassword"></i>
                        </div>
                        <div class="password-strength">Password strength: <span id="strength-text">Weak</span></div>
                        <div class="strength-meter">
                            <div class="strength-meter-fill" id="strength-meter"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-user-plus"></i> Add Teacher
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Password strength indicator
        passwordField.addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthText = document.getElementById('strength-text');
            const strengthMeter = document.getElementById('strength-meter');
            let strength = 0;
            
            // Check password length
            if (password.length >= 6) strength += 1;
            if (password.length >= 8) strength += 1;
            
            // Check for mixed case
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
            
            // Check for numbers
            if (password.match(/([0-9])/)) strength += 1;
            
            // Check for special chars
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
            
            // Update UI
            let strengthName = '';
            let strengthColor = '';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthName = 'Weak';
                    strengthColor = '#ff4444';
                    break;
                case 2:
                    strengthName = 'Fair';
                    strengthColor = '#ffbb33';
                    break;
                case 3:
                    strengthName = 'Good';
                    strengthColor = '#00C851';
                    break;
                case 4:
                case 5:
                    strengthName = 'Strong';
                    strengthColor = '#00C851';
                    break;
            }
            
            strengthText.textContent = strengthName;
            strengthMeter.style.width = (strength * 20) + '%';
            strengthMeter.style.backgroundColor = strengthColor;
        });
    </script>
</body>
</html>




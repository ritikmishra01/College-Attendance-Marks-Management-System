<?php
include('../includes/auth.php');
include('../includes/db.php');

redirectIfNotLoggedIn();
redirectIfNotRole('admin');

$username = $_SESSION['username'];
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword)) {
        $message = "Password cannot be empty.";
        $message_type = 'error';
    } elseif (strlen($newPassword) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = 'error';
    } elseif ($newPassword === $confirmPassword) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hashed, $username]);
        $message = "Password updated successfully!";
        $message_type = 'success';
    } else {
        $message = "Passwords do not match.";
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Admin Panel</title>
    <style>
        :root {
            --primary-color: #3498db;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --light-gray: #f5f5f5;
            --dark-gray: #333;
            --border-radius: 4px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-gray);
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 500px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
        }
        
        h2 {
            color: var(--primary-color);
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .password-strength {
            height: 4px;
            background: #eee;
            margin-top: 8px;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            background: #e74c3c;
            transition: width 0.3s, background 0.3s;
        }
        
        button {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .password-requirements {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Change Admin Password</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" id="passwordForm">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required 
                       minlength="8" placeholder="Enter new password">
                <div class="password-strength">
                    <div class="strength-meter" id="strengthMeter"></div>
                </div>
                <div class="password-requirements">
                    Must be at least 8 characters long
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Re-enter new password">
            </div>

            <button type="submit">Change Password</button>
            <a href="index.php" class="back-link">← Back to Admin Dashboard</a>
        </form>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('new_password');
        const strengthMeter = document.getElementById('strengthMeter');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Character diversity
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update meter
            const width = Math.min(strength * 25, 100);
            let color = '#e74c3c'; // red
            
            if (strength >= 3) color = '#f39c12'; // orange
            if (strength >= 5) color = '#2ecc71'; // green
            
            strengthMeter.style.width = width + '%';
            strengthMeter.style.backgroundColor = color;
        });
        
        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
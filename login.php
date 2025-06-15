<?php
session_start();
include('includes/db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            if ($user['role'] === 'admin') {
                header('Location: admin/admin.php');
            } elseif ($user['role'] === 'teacher') {
                header('Location: dashboard/teacher.php');
            } elseif ($user['role'] === 'student') {
                header('Location: dashboard/student.php');
            }
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | Academic Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      color: #212529;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
      line-height: 1.6;
    }
    .login-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      width: 100%;
      max-width: 480px;
      padding: 40px;
      border: 1px solid #e9ecef;
      margin-bottom: 40px;
    }
    .login-header {
      text-align: center;
      margin-bottom: 40px;
    }
    img.logo {
      width: 100px;
      height: 100px;
      object-fit: contain;
      margin-bottom: 24px;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    }
    h1 {
      font-weight: 700;
      font-size: 2rem;
      line-height: 1.2;
      margin-bottom: 16px;
      color: #2b2d42;
      letter-spacing: -0.5px;
    }
    .login-subtitle {
      font-weight: 400;
      font-size: 1rem;
      color: #6c757d;
      max-width: 80%;
      margin: 0 auto;
    }
    .login-form {
      margin-top: 30px;
    }
    .form-group {
      margin-bottom: 20px;
      position: relative;
    }
    .form-control {
      width: 100%;
      padding: 14px 16px;
      font-size: 1rem;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    .form-control:focus {
      border-color: #4361ee;
      outline: none;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    .toggle-password {
      position: absolute;
      top: 50%;
      right: 16px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6c757d;
    }
    .btn {
      width: 100%;
      padding: 14px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .btn-primary {
      background-color: #4361ee;
      color: white;
    }
    .btn-primary:hover {
      background-color: #3a0ca3;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(58, 12, 163, 0.2);
    }
    .alert {
      padding: 14px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 0.95rem;
    }
    .alert-danger {
      background-color: rgba(247, 37, 133, 0.1);
      border: 1px solid rgba(247, 37, 133, 0.2);
      color: #f72585;
    }
    footer {
      margin-top: 40px;
      text-align: center;
      color: #6c757d;
      font-size: 0.85rem;
    }
    footer p {
      margin-bottom: 8px;
    }
    @media (max-width: 768px) {
      .login-card {
        padding: 30px;
      }
      h1 {
        font-size: 1.8rem;
      }
    }
    @media (max-width: 480px) {
      .login-card {
        padding: 25px 20px;
      }
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-header">
      <img src="assets/images/logo.png" alt="University Logo" class="logo" />
      <h1>Academic Management System</h1>
      <p class="login-subtitle">Sign in to access your account</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="login-form">
      <div class="form-group">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
      </div>

      <div class="form-group" style="margin-bottom: 5px;">
        <input type="password" class="form-control" name="password" id="passwordInput" placeholder="Password" required>
        <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
      </div>

      <div style="text-align: right; margin-bottom: 20px;">
        <a href="forgot-password.php" style="font-size: 0.9rem; color: #4361ee; text-decoration: none;">Forgot Password?</a>
      </div>

      <button type="submit" class="btn btn-primary">Login</button>
    </form>
  </div>

  <footer>
    <p>RITIK MISHRA - Committed to Academic Excellence</p>
    <p>© <?= date('Y') ?> Academic Management System. All rights reserved.</p>
  </footer>

  <script>
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("passwordInput");

    togglePassword.addEventListener("click", function () {
      const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);
      this.classList.toggle("fa-eye-slash");
    });
  </script>
</body>
</html>

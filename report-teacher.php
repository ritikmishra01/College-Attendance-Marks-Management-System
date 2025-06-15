<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();
redirectIfNotRole('teacher');

$teacher = $_SESSION['username'];

// Fetch available divisions
$divisions = $pdo->query("SELECT DISTINCT division FROM users WHERE role = 'student' ORDER BY division")->fetchAll(PDO::FETCH_COLUMN);

$subject = $_GET['subject'] ?? '';
$exam_type = $_GET['exam_type'] ?? '';
$division = $_GET['division'] ?? '';

$students = [];

if ($subject && $exam_type && $division) {
    $stmt = $pdo->prepare("
        SELECT u.username, u.roll, u.name, m.marks
        FROM users u
        LEFT JOIN marks m ON u.username = m.student_username AND m.subject = ? AND m.exam_type = ?
        WHERE u.role = 'student' AND u.division = ?
        ORDER BY u.roll
    ");
    $stmt->execute([$subject, $exam_type, $division]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_marks'])) {
    foreach ($_POST['marks'] as $username => $mark) {
        $mark = trim($mark);

        // Check if entry exists
        $checkStmt = $pdo->prepare("SELECT id FROM marks WHERE student_username = ? AND subject = ? AND exam_type = ?");
        $checkStmt->execute([$username, $subject, $exam_type]);
        $existing = $checkStmt->fetchColumn();

        if ($existing) {
            $updateStmt = $pdo->prepare("UPDATE marks SET marks = ? WHERE id = ?");
            $updateStmt->execute([$mark, $existing]);
        } else {
           $insertStmt = $pdo->prepare("INSERT INTO marks (student_username, subject, exam_type, marks, teacher) VALUES (?, ?, ?, ?, ?)");
           $insertStmt->execute([$username, $subject, $exam_type, $mark, $teacher]);

        }
    }

    header("Location: report-teacher.php?subject=$subject&exam_type=$exam_type&division=$division&updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marks Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #28a745;
            --light-gray: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .header {
            background-color: var(--secondary-color);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            font-weight: 500;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: var(--light-gray);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border-color: rgba(40, 167, 69, 0.3);
            color: #155724;
        }
        
        .mark-input {
            width: 80px;
            text-align: center;
        }
        
        .no-students {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }
        
        .back-btn {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: opacity 0.2s;
        }
        
        .back-btn:hover {
            opacity: 0.8;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="../dashboard/teacher.php" class="back-btn">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
        <div class="container text-center">
            <h1 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Marks Management System</h1>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> Marks updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <h3 class="mb-4"><i class="fas fa-filter me-2"></i>Filter Students</h3>
            <form method="GET" action="report-teacher.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" 
                               value="<?= htmlspecialchars($subject) ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="exam_type" class="form-label">Exam Type</label>
                        <select class="form-select" id="exam_type" name="exam_type" required>
                            <option value="">Select Exam Type</option>
                            <option value="Midterm" <?= $exam_type === 'Midterm' ? 'selected' : '' ?>>Midterm</option>
                            <option value="Final" <?= $exam_type === 'Final' ? 'selected' : '' ?>>Final</option>
                            <option value="Quiz" <?= $exam_type === 'Quiz' ? 'selected' : '' ?>>Quiz</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="division" class="form-label">Division</label>
                        <select class="form-select" id="division" name="division" required>
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $div): ?>
                                <option value="<?= htmlspecialchars($div) ?>" <?= $division === $div ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($div) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>View Students
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($students): ?>
            <div class="table-container">
                <h3 class="mb-4">
                    <i class="fas fa-list-ol me-2"></i>
                    Marks for <?= htmlspecialchars($subject) ?> - <?= htmlspecialchars($exam_type) ?> - <?= htmlspecialchars($division) ?>
                </h3>
                
                <form method="POST">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                    <th>Marks (0-100)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $stu): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($stu['roll']) ?></td>
                                        <td><?= htmlspecialchars($stu['name']) ?></td>
                                        <td>
                                            <input type="number" class="form-control mark-input" 
                                                   name="marks[<?= htmlspecialchars($stu['username']) ?>]" 
                                                   value="<?= is_numeric($stu['marks']) ? $stu['marks'] : '' ?>" 
                                                   min="0" max="100" required>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-end mt-3">
                        <button type="submit" name="update_marks" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        <?php elseif ($subject && $exam_type && $division): ?>
            <div class="no-students">
                <i class="fas fa-user-graduate fa-3x mb-3" style="color: #adb5bd;"></i>
                <h4>No Students Found</h4>
                <p class="text-muted">There are no students registered in <?= htmlspecialchars($division) ?> division.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alert after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            var alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(function() {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            }
        });
    </script>
</body>
</html>
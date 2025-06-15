<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Only teacher access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$teacher = $_SESSION['username'];
$message = '';

// Fetch divisions dynamically
$stmt = $pdo->query("SELECT DISTINCT division FROM users WHERE role = 'student' ORDER BY division");
$divisions = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $exam_type = trim($_POST['exam_type']);
    $division = $_POST['division'] ?? '';
    $marksData = $_POST['marks'] ?? [];

    if (empty($subject) || empty($exam_type) || empty($division)) {
        $message = '<div class="alert alert-danger">Please fill all required fields.</div>';
    } else {
        try {
            $pdo->beginTransaction();
            
            foreach ($marksData as $student_username => $marks) {
                $marks = intval($marks);

                // Check if marks record exists
                $check = $pdo->prepare("SELECT id FROM marks WHERE student_username = ? AND subject = ? AND exam_type = ?");
                $check->execute([$student_username, $subject, $exam_type]);
                
                if ($check->rowCount() > 0) {
                    // Update marks
                    $update = $pdo->prepare("UPDATE marks SET marks = ?, teacher = ? WHERE student_username = ? AND subject = ? AND exam_type = ?");
                    $update->execute([$marks, $teacher, $student_username, $subject, $exam_type]);
                } else {
                    // Insert marks
                    $insert = $pdo->prepare("INSERT INTO marks (student_username, subject, exam_type, marks, teacher) VALUES (?, ?, ?, ?, ?)");
                    $insert->execute([$student_username, $subject, $exam_type, $marks, $teacher]);
                }
            }
            
            $pdo->commit();
            $message = '<div class="alert alert-success">Marks saved successfully!</div>';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger">Error saving marks: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Fetch students for selected division (if any)
$students = [];
if (!empty($_POST['division'])) {
    $stmt = $pdo->prepare("SELECT username, name, roll FROM users WHERE role = 'student' AND division = ? ORDER BY roll");
    $stmt->execute([$_POST['division']]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Marks | Teacher Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .marks-input {
            width: 80px;
            text-align: center;
        }
        .nav-tabs .nav-link.active {
            font-weight: 600;
        }
        .page-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-journal-check"></i> Marks Entry System</h2>
                <a href="../dashboard/teacher.php" class="btn btn-outline-secondary">

                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php echo $message; ?>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Enter Marks</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="enter.php" id="marksForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="exam_type" class="form-label">Exam Type</label>
                            <select class="form-select" id="exam_type" name="exam_type" required>
                                <option value="">Select Exam Type</option>
                                <option value="Midterm" <?= (($_POST['exam_type'] ?? '') == 'Midterm') ? 'selected' : '' ?>>Midterm</option>
                                <option value="Final" <?= (($_POST['exam_type'] ?? '') == 'Final') ? 'selected' : '' ?>>Final</option>
                                <option value="Quiz" <?= (($_POST['exam_type'] ?? '') == 'Quiz') ? 'selected' : '' ?>>Quiz</option>
                                <option value="Assignment" <?= (($_POST['exam_type'] ?? '') == 'Assignment') ? 'selected' : '' ?>>Assignment</option>
                                <option value="Project" <?= (($_POST['exam_type'] ?? '') == 'Project') ? 'selected' : '' ?>>Project</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="division" class="form-label">Division</label>
                            <select class="form-select" id="division" name="division" onchange="this.form.submit()" required>
                                <option value="">Select Division</option>
                                <?php foreach ($divisions as $div): ?>
                                    <option value="<?= htmlspecialchars($div) ?>" <?= (($_POST['division'] ?? '') == $div) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($div) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($students): ?>
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="mb-0">
                    <i class="bi bi-people-fill"></i> Students in Division <?= htmlspecialchars($_POST['division']) ?>
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="enter.php">
                    <input type="hidden" name="subject" value="<?= htmlspecialchars($_POST['subject']) ?>">
                    <input type="hidden" name="exam_type" value="<?= htmlspecialchars($_POST['exam_type']) ?>">
                    <input type="hidden" name="division" value="<?= htmlspecialchars($_POST['division']) ?>">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%">Roll No</th>
                                    <th width="45%">Student Name</th>
                                    <th width="40%">Marks (0-100)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $stu): ?>
                                    <?php
                                        // Get existing marks if any
                                        $stmt = $pdo->prepare("SELECT marks FROM marks WHERE student_username = ? AND subject = ? AND exam_type = ?");
                                        $stmt->execute([$stu['username'], $_POST['subject'], $_POST['exam_type']]);
                                        $existingMarks = $stmt->fetchColumn();
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($stu['roll']) ?></td>
                                        <td><?= htmlspecialchars($stu['name']) ?></td>
                                        <td>
                                            <input type="number" class="form-control marks-input" 
                                                   name="marks[<?= htmlspecialchars($stu['username']) ?>]" 
                                                   min="0" max="100" 
                                                   value="<?= htmlspecialchars($existingMarks ?: '') ?>" 
                                                   required>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <button type="reset" class="btn btn-outline-secondary me-md-2">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Marks
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Input validation for marks
        document.querySelectorAll('.marks-input').forEach(input => {
            input.addEventListener('change', function() {
                if (this.value < 0) this.value = 0;
                if (this.value > 100) this.value = 100;
            });
        });
    </script>
</body>
</html>
<?php
include('../includes/auth.php');
include('../includes/db.php');
redirectIfNotLoggedIn();
redirectIfNotRole('teacher');

$teacher = $_SESSION['username'];

// Handle CSV download
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download'])) {
    $subject = $_POST['subject'];
    $division = $_POST['division'];

    // Get total sessions
    $sess_stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE subject = ? AND teacher = ? AND division = ?");
    $sess_stmt->execute([$subject, $teacher, $division]);
    $total_sessions = $sess_stmt->fetchColumn();

    $defaulters = [];
    if ($total_sessions > 0) {
        $stu_stmt = $pdo->prepare("SELECT username, name, roll FROM users WHERE role = 'student' AND division = ?");
        $stu_stmt->execute([$division]);
        $students = $stu_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($students as $stu) {
            $username = $stu['username'];
            $att_stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_logs 
                WHERE student_username = ? 
                AND session_id IN (
                    SELECT id FROM attendance_sessions WHERE subject = ? AND teacher = ? AND division = ?
                )");
            $att_stmt->execute([$username, $subject, $teacher, $division]);
            $present = $att_stmt->fetchColumn();
            $percentage = ($present / $total_sessions) * 100;

            if ($percentage < 75) {
                $defaulters[] = [
                    'roll' => $stu['roll'],
                    'name' => $stu['name'],
                    'username' => $username,
                    'percentage' => round($percentage, 2)
                ];
            }
        }
    }

    // Output CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=defaulter_list.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Roll No', 'Name', 'Username', 'Attendance %']);
    foreach ($defaulters as $d) {
        fputcsv($output, [$d['roll'], $d['name'], $d['username'], $d['percentage']]);
    }
    fclose($output);
    exit;
}

// If GET request, show the form and result
$subject = $_GET['subject'] ?? '';
$division = $_GET['division'] ?? '';
$defaulters = [];
$total_sessions = 0;

if ($subject && $division) {
    $sess_stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE subject = ? AND teacher = ? AND division = ?");
    $sess_stmt->execute([$subject, $teacher, $division]);
    $total_sessions = $sess_stmt->fetchColumn();

    if ($total_sessions > 0) {
        $stu_stmt = $pdo->prepare("SELECT username, name, roll FROM users WHERE role = 'student' AND division = ?");
        $stu_stmt->execute([$division]);
        $students = $stu_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($students as $stu) {
            $username = $stu['username'];
            $att_stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_logs 
                WHERE student_username = ? 
                AND session_id IN (
                    SELECT id FROM attendance_sessions WHERE subject = ? AND teacher = ? AND division = ?
                )");
            $att_stmt->execute([$username, $subject, $teacher, $division]);
            $present = $att_stmt->fetchColumn();
            $percentage = ($present / $total_sessions) * 100;

            if ($percentage < 75) {
                $defaulters[] = [
                    'roll' => $stu['roll'],
                    'name' => $stu['name'],
                    'username' => $username,
                    'percentage' => round($percentage, 2)
                ];
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
    <title>Defaulter List | Attendance System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .badge-attendance {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }
        .btn-download {
            background-color: #28a745;
            color: white;
        }
        .btn-dashboard {
            background-color: #6c757d;
            color: white;
        }
        .btn-download:hover {
            background-color: #218838;
            color: white;
        }
        .btn-dashboard:hover {
            background-color: #5a6268;
            color: white;
        }
        .percentage-cell {
            font-weight: 500;
        }
        .percentage-low {
            color: #dc3545;
        }
        .percentage-medium {
            color: #ffc107;
        }
        .percentage-high {
            color: #28a745;
        }
        .navbar-custom {
            background-color: #343a40;
            padding: 0.5rem 1rem;
        }
        .navbar-brand {
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
    <!-- Simple Header -->
    <div class="container-fluid bg-primary text-white py-2">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-clipboard2-check"></i> Attendance System
                </h4>
                <a href="../dashboard/teacher.php" class="btn btn-secondary">
                    <i class="bi bi-speedometer2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <div class="container py-4">
        <div class="page-header">
            <h2><i class="bi bi-people-fill"></i> Defaulter List Management</h2>
            <p class="text-muted">Generate and download attendance defaulters report</p>
        </div>

        <div class="row mb-4">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-search"></i> Search Criteria</h4>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           value="<?= htmlspecialchars($subject) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="division" class="form-label">Division</label>
                                    <input type="text" class="form-control" id="division" name="division" 
                                           value="<?= htmlspecialchars($division) ?>" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-search"></i> Show Defaulters
                                    </button>
                                    <a href="../dashboard/teacher.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($subject && $division): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="bi bi-list-check"></i> Defaulter List for 
                                <?= htmlspecialchars($subject) ?> - Division <?= htmlspecialchars($division) ?>
                            </h4>
                            <?php if (count($defaulters) > 0): ?>
                            <div>
                                <form method="post" action="defaulter_list.php" class="d-inline me-2">
                                    <input type="hidden" name="subject" value="<?= htmlspecialchars($subject) ?>">
                                    <input type="hidden" name="division" value="<?= htmlspecialchars($division) ?>">
                                    <input type="hidden" name="download" value="1">
                                    <button type="submit" class="btn btn-download">
                                        <i class="bi bi-download"></i> Download CSV
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-secondary badge-attendance">
                                Total Sessions: <?= $total_sessions ?>
                            </span>
                            <span class="badge bg-info text-dark badge-attendance ms-2">
                                Defaulters: <?= count($defaulters) ?>
                            </span>
                        </div>

                        <?php if (count($defaulters) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Roll No</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th class="text-end">Attendance %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($defaulters as $d): 
                                        $percentageClass = '';
                                        if ($d['percentage'] < 50) {
                                            $percentageClass = 'percentage-low';
                                        } elseif ($d['percentage'] < 65) {
                                            $percentageClass = 'percentage-medium';
                                        } else {
                                            $percentageClass = 'percentage-high';
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $d['roll'] ?></td>
                                        <td><?= $d['name'] ?></td>
                                        <td><?= $d['username'] ?></td>
                                        <td class="text-end percentage-cell <?= $percentageClass ?>">
                                            <?= $d['percentage'] ?>%
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle-fill"></i> No defaulters found. All students have attendance above 75%.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
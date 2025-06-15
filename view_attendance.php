<?php
include('../includes/auth.php');
include('../includes/db.php');
redirectIfNotLoggedIn();
redirectIfNotRole('teacher');

$teacher = $_SESSION['username'];

// Capture filter inputs
$filter_subject = $_GET['subject'] ?? '';
$filter_date = $_GET['date'] ?? '';

// Modify SQL with filters
$query = "SELECT * FROM attendance_sessions WHERE teacher = ?";
$params = [$teacher];

if (!empty($filter_subject)) {
    $query .= " AND subject LIKE ?";
    $params[] = '%' . $filter_subject . '%';
}
if (!empty($filter_date)) {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $filter_date;
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sessions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
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
        .session-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .session-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .session-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .session-body {
            padding: 1.5rem;
        }
        .table-responsive {
            margin-top: 1rem;
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
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stats-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .no-sessions {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .timestamp {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .reason-text {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Attendance Report</h1>
            <a href="../dashboard/teacher.php" class="btn btn-light text-dark">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <?php if (empty($sessions)): ?>
            <div class="no-sessions">
                <i class="fas fa-calendar-times fa-3x mb-3" style="color: #adb5bd;"></i>
                <h3>No Attendance Sessions Found</h3>
                <p class="text-muted">You haven't created any attendance sessions yet.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($sessions as $session): ?>
            <div class="session-card">
                <div class="session-header">
                    <div>
                        <h4 class="mb-0"><?= htmlspecialchars($session['subject']) ?></h4>
                        <small class="timestamp">Created: <?= date('M j, Y g:i A', strtotime($session['created_at'])) ?></small>
                    </div>
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-hashtag me-1"></i><?= htmlspecialchars($session['code']) ?>
                    </span>
                </div>

                <div class="session-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="stats-card text-center">
                                <h6 class="text-uppercase text-muted">Present Students</h6>
                                <?php
                                $sid = $session['id'];
                                $present_count = $pdo->prepare("SELECT COUNT(*) FROM attendance_logs WHERE session_id = ?");
                                $present_count->execute([$sid]);
                                $present = $present_count->fetchColumn();
                                ?>
                                <div class="stats-value text-success"><?= $present ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card text-center">
                                <h6 class="text-uppercase text-muted">Denied Students</h6>
                                <?php
                                $denied_count = $pdo->prepare("SELECT COUNT(*) FROM attendance_denied WHERE session_id = ?");
                                $denied_count->execute([$sid]);
                                $denied = $denied_count->fetchColumn();
                                ?>
                                <div class="stats-value text-danger"><?= $denied ?></div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Present Students</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Username</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $success = $pdo->prepare("SELECT * FROM attendance_logs WHERE session_id = ?");
                                $success->execute([$sid]);
                                $records = $success->fetchAll();
                                foreach ($records as $s): ?>
                                    <tr>
                                        <td><?= $s['roll_number'] ?></td>
                                        <td><?= htmlspecialchars($s['student_username']) ?></td>
                                        <td><?= date('M j, g:i A', strtotime($s['timestamp'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (count($records) == 0): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No students marked as present</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-4 mb-3"><i class="fas fa-times-circle text-danger me-2"></i>Denied Students</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Username</th>
                                    <th>Reason</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $denied_result = $pdo->prepare("SELECT * FROM attendance_denied WHERE session_id = ?");
                                $denied_result->execute([$sid]);
                                $records = $denied_result->fetchAll();
                                foreach ($records as $d): ?>
                                    <tr>
                                        <td><?= $d['roll_number'] ?></td>
                                        <td><?= htmlspecialchars($d['student_username']) ?></td>
                                        <td class="reason-text" title="<?= htmlspecialchars($d['reason']) ?>"><?= htmlspecialchars($d['reason']) ?></td>
                                        <td><?= date('M j, g:i A', strtotime($d['timestamp'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (count($records) == 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No students were denied</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Filter Section -->
        <div class="card mt-4 mb-5 p-4">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Sessions</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control" placeholder="e.g. Physics" value="<?= htmlspecialchars($filter_subject) ?>">
                </div>
                <div class="col-md-5">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Apply
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>

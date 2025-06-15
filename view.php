<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();
redirectIfNotRole('student');

$username = $_SESSION['username'];

// Get the student's division
$stmt = $pdo->prepare("SELECT division FROM users WHERE username = ?");
$stmt->execute([$username]);
$division = $stmt->fetchColumn();

// Fetch course materials for this division
$stmt = $pdo->prepare("SELECT * FROM course_materials WHERE division = ? ORDER BY uploaded_at DESC");
$stmt->execute([$division]);
$materials = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Course Materials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Course Materials for Division <?= htmlspecialchars($division) ?></h2>

    <?php if (empty($materials)): ?>
        <p class="text-muted">No course materials available for your division.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $material): ?>
                    <tr>
                        <td><?= htmlspecialchars($material['title']) ?></td>
                        <td><?= htmlspecialchars($material['subject']) ?></td>
                        <td><?= htmlspecialchars($material['uploaded_by']) ?></td>
                        <td><?= date("d M Y, h:i A", strtotime($material['uploaded_at'])) ?></td>
                        <td><a href="../uploads/<?= htmlspecialchars($material['filename']) ?>" class="btn btn-sm btn-success" target="_blank">Download</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="../dashboard/student.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
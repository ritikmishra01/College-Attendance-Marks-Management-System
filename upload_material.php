<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

redirectIfNotLoggedIn();
redirectIfNotRole('teacher');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $subject = trim($_POST['subject']);
    $division = trim($_POST['division']);
    $uploaded_by = $_SESSION['username'];

    if (!$title || !$subject || !$division || !isset($_FILES['material'])) {
        $message = "All fields are required.";
    } else {
        $file = $_FILES['material'];
        $filename = basename($file['name']);
        $targetDir = '../uploads/';
        $newFileName = time() . '_' . $filename;
        $targetPath = $targetDir . $newFileName;

        // Create uploads folder if not exists
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("INSERT INTO course_materials (title, filename, subject, uploaded_by, division) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $newFileName, $subject, $uploaded_by, $division]);
            $message = "Material uploaded successfully!";
        } else {
            $message = "Failed to upload file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Course Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Upload Course Material</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Material Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" name="subject" id="subject" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="division" class="form-label">Division</label>
            <select name="division" id="division" class="form-select" required>
                <option value="">-- Select Division --</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <!-- Add more divisions if needed -->
            </select>
        </div>

        <div class="mb-3">
            <label for="material" class="form-label">Select File</label>
            <input type="file" name="material" id="material" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="../dashboard/teacher.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>

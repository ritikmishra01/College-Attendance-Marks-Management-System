<?php
include('../includes/auth.php');
redirectIfNotLoggedIn();
redirectIfNotRole('teacher');

// Ensure session variables are set
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = 'Teacher';
}
if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--secondary-color), var(--dark-color));
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 5px;
            border-radius: 5px;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        .quick-actions .btn {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .logout-btn {
            background-color: var(--accent-color);
            border: none;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        .nav-tabs .nav-link {
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .material-item {
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }
        
        .material-item:hover {
            transform: translateX(5px);
        }
        
        .file-icon {
            font-size: 2rem;
            color: var(--primary-color);
        }
        
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            border-color: var(--primary-color);
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .stats-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .stats-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-description {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="text-center mb-4">
                    <h4>Teacher Portal</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../attendance/session.php">
                            <i class="fas fa-calendar-plus"></i> Create Session
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../attendance/view_attendance.php">
                            <i class="fas fa-clipboard-list"></i> Attendance Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../attendance/defaulter_list.php">
                            <i class="fas fa-exclamation-triangle"></i> Defaulter List
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../marks/enter.php">
                            <i class="fas fa-edit"></i> Enter Marks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../marks/report-teacher.php">
                            <i class="fas fa-chart-bar"></i> Marks Report
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../courses/upload_material.php">
                            <i class="fas fa-upload"></i> Upload Materials
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../courses/teacher_view.php">
                            <i class="fas fa-book"></i> View Materials
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../teacher/change_password.php">
                            <i class="fas fa-key"></i> Change Password
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="header">
                    <h2>Welcome, <span class="text-primary"><?= htmlspecialchars($_SESSION['name']) ?></span></h2>
                    <div class="user-profile">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=3498db&color=fff" alt="Profile">
                        <?php if (!empty($_SESSION['email'])): ?>
                            <span><?= htmlspecialchars($_SESSION['email']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dashboard Tabs -->
                <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials" type="button" role="tab">Teaching Materials</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="quickactions-tab" data-bs-toggle="tab" data-bs-target="#quickactions" type="button" role="tab">Quick Actions</button>
                    </li>
                </ul>

                <div class="tab-content" id="dashboardTabsContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card stats-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Attendance Tracking</h5>
                                        <p class="stat-description">Monitor and manage student attendance records efficiently</p>
                                        <a href="../attendance/view_attendance.php" class="btn btn-sm btn-primary">View Attendance</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stats-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Student Performance</h5>
                                        <p class="stat-description">Track and analyze student academic progress</p>
                                        <a href="../marks/report-teacher.php" class="btn btn-sm btn-primary">View Reports</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stats-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Teaching Resources</h5>
                                        <p class="stat-description">Access and share educational materials with students</p>
                                        <a href="../courses/teacher_view.php" class="btn btn-sm btn-primary">View Materials</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-bell me-2"></i> Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <div class="list-group-item border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-3"><i class="fas fa-check-circle"></i></span>
                                            <div>
                                                <h6 class="mb-1">Attendance marked successfully</h6>
                                                <small class="text-muted">Track your recent attendance sessions</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="list-group-item border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-success me-3"><i class="fas fa-upload"></i></span>
                                            <div>
                                                <h6 class="mb-1">New materials uploaded</h6>
                                                <small class="text-muted">Students can now access your latest resources</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="list-group-item border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-warning me-3"><i class="fas fa-edit"></i></span>
                                            <div>
                                                <h6 class="mb-1">Marks updated</h6>
                                                <small class="text-muted">Review your recent grading activity</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Materials Tab -->
                    <div class="tab-pane fade" id="materials" role="tabpanel">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0"><i class="fas fa-book me-2"></i> Teaching Materials</h5>
                                        <a href="../courses/upload_material.php" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> Upload New</a>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i> Upload your course materials to share with students
                                        </div>
                                        <div class="list-group">
                                            <div class="list-group-item border-0 py-3 material-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="file-icon me-3">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Course Syllabus</h6>
                                                        <small class="text-muted">Essential document outlining course objectives</small>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Download</a></li>
                                                            <li><a class="dropdown-item" href="#"><i class="fas fa-share me-2"></i>Share</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="list-group-item border-0 py-3 material-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="file-icon me-3">
                                                        <i class="fas fa-file-word"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">Assignment Guidelines</h6>
                                                        <small class="text-muted">Detailed instructions for student assignments</small>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Download</a></li>
                                                            <li><a class="dropdown-item" href="#"><i class="fas fa-share me-2"></i>Share</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-upload me-2"></i> Upload New Material</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                                            <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
                                            <h5>Drag & Drop files here</h5>
                                            <p class="text-muted">or click to browse</p>
                                            <input type="file" id="fileInput" style="display: none;" multiple>
                                        </div>
                                        <form class="mt-3">
                                            <div class="mb-3">
                                                <label for="courseSelect" class="form-label">Course</label>
                                                <select class="form-select" id="courseSelect">
                                                    <option selected disabled>Select course</option>
                                                    <option>Introduction to Programming</option>
                                                    <option>Data Structures</option>
                                                    <option>Database Systems</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="materialTitle" class="form-label">Material Title</label>
                                                <input type="text" class="form-control" id="materialTitle" placeholder="e.g. Lecture Slides Week 1">
                                            </div>
                                            <div class="mb-3">
                                                <label for="materialDescription" class="form-label">Description</label>
                                                <textarea class="form-control" id="materialDescription" rows="2" placeholder="Brief description of the material"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">Upload Material</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Tab -->
                    <div class="tab-pane fade" id="quickactions" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i> Quick Actions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="../attendance/session.php" class="btn btn-primary text-start">
                                                <i class="fas fa-calendar-plus me-2"></i> New Attendance Session
                                            </a>
                                            <a href="../marks/enter.php" class="btn btn-success text-start">
                                                <i class="fas fa-edit me-2"></i> Enter Marks
                                            </a>
                                            <a href="../attendance/defaulter_list.php" class="btn btn-warning text-start">
                                                <i class="fas fa-exclamation-triangle me-2"></i> View Defaulters
                                            </a>
                                            <a href="../marks/report-teacher.php" class="btn btn-info text-start">
                                                <i class="fas fa-chart-bar me-2"></i> Generate Report
                                            </a>
                                            <a href="../courses/upload_material.php" class="btn btn-secondary text-start">
                                                <i class="fas fa-upload me-2"></i> Upload Teaching Material
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-lightbulb me-2"></i> Teaching Tips</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-success">
                                            <h6><i class="fas fa-check-circle me-2"></i> Best Practice</h6>
                                            <p class="mb-0">Regular attendance tracking improves student engagement and performance.</p>
                                        </div>
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle me-2"></i> Did You Know?</h6>
                                            <p class="mb-0">You can upload multiple file types including PDFs, Word docs, and PowerPoint slides.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple file upload feedback
        document.getElementById('fileInput').addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const uploadArea = document.querySelector('.upload-area');
                const fileName = this.files[0].name;
                uploadArea.innerHTML = `
                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                    <h5>${fileName}</h5>
                    <p class="text-muted">Ready to upload</p>
                `;
            }
        });
    </script>
</body>
</html>
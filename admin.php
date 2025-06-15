<?php
include('../includes/auth.php');
include('../includes/db.php');
redirectIfNotLoggedIn();
redirectIfNotRole('admin');

// Fetch all teachers and students
$teachers = $pdo->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY name")->fetchAll();
$students = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY division, roll")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        h1 {
            color: var(--primary);
            font-size: 28px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info i {
            color: var(--primary);
            font-size: 20px;
        }
        
        nav {
            margin-bottom: 30px;
        }
        
        .nav-menu {
            display: flex;
            gap: 15px;
            list-style: none;
        }
        
        .nav-menu a {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        h2 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 22px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background-color: var(--light);
        }
        
        tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-edit {
            background-color: #4cc9f0;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #3ab7de;
        }
        
        .btn-delete {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #e5177e;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-teacher {
            background-color: #f0f4ff;
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        
        .badge-student {
            background-color: #fff0f6;
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .nav-menu {
                flex-wrap: wrap;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <div class="user-info">
                <i class="fas fa-user-shield"></i>
                <span>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></span>
            </div>
        </header>

        <nav>
            <ul class="nav-menu">
                <li><a href="add_teacher.php"><i class="fas fa-user-plus"></i> Add Teacher</a></li>
                <li><a href="add_student.php"><i class="fas fa-user-graduate"></i> Add Student</a></li>
                <a class="nav-link" href="../admin/change_password.php"></i class="fas fa-key"></i> Change Password</a></li>

        

                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <div class="section">
            <h2><i class="fas fa-chalkboard-teacher"></i> Teachers</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td>
                                <span class="badge badge-teacher">
                                    <i class="fas fa-user-tie"></i> <?= htmlspecialchars($teacher['username']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($teacher['name']) ?></td>
                            <td class="actions">
                                <a href="edit_teacher.php?username=<?= urlencode($teacher['username']) ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_teacher.php?username=<?= urlencode($teacher['username']) ?>" 
                                   class="btn btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this teacher?')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2><i class="fas fa-user-graduate"></i> Students</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Division</th>
                        <th>Roll</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <span class="badge badge-student">
                                    <i class="fas fa-user-graduate"></i> <?= htmlspecialchars($student['username']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td><?= htmlspecialchars($student['division']) ?></td>
                            <td><?= htmlspecialchars($student['roll']) ?></td>
                            <td class="actions">
                                <a href="edit_student.php?username=<?= urlencode($student['username']) ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_student.php?username=<?= urlencode($student['username']) ?>" 
                                   class="btn btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this student?')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
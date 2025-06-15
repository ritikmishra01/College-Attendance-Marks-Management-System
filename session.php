<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Only allow logged in teachers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$message_type = ''; // For styling success/error messages

// On form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher = $_SESSION['username'];
    $subject = trim($_POST['subject']);
    $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT); // Auto-generated 4-digit code
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $division = isset($_POST['division']) ? trim($_POST['division']) : '';

    if (empty($subject) || !$latitude || !$longitude || empty($division)) {
        $message = "All fields including location and division are required.";
        $message_type = 'error';
    } else {
        // Check if code already exists for this teacher (optional)
        $stmt = $pdo->prepare("SELECT * FROM attendance_sessions WHERE teacher = ? AND code = ?");
        $stmt->execute([$teacher, $code]);
        if ($stmt->rowCount() > 0) {
            $message = "You already have a session with this code. Please try again.";
            $message_type = 'error';
        } else {
            // Insert session with division (created_at auto set by DB)
            $stmt = $pdo->prepare("INSERT INTO attendance_sessions (teacher, subject, code, latitude, longitude, division) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$teacher, $subject, $code, $latitude, $longitude, $division])) {
                $message = "✅ Attendance session created successfully!<br><strong>Session Code: $code</strong>";
                $message_type = 'success';
            } else {
                $message = "Error creating session. Please try again.";
                $message_type = 'error';
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
    <title>Create Attendance Session | Teacher Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #edf2ff;
            --success: #4cc9f0;
            --error: #f94144;
            --text: #2b2d42;
            --light-gray: #e9ecef;
            --border-radius: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--text);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 600px;
            padding: 2rem;
        }

        .session-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
        }

        h2 {
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-weight: 600;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text);
        }

        input, select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border 0.3s, box-shadow 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }

        button:hover {
            background-color: #3a56d4;
        }

        .message {
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            margin: 1.5rem 0;
            font-weight: 500;
            text-align: center;
        }

        .success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .error {
            background-color: rgba(249, 65, 68, 0.1);
            color: var(--error);
            border: 1px solid var(--error);
        }

        .location-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .location-icon {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 600px) {
            .session-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="session-card">
        <h2>Create Attendance Session</h2>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="post" id="sessionForm">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" name="subject" id="subject" required placeholder="Enter subject name">
            </div>

            <div class="form-group">
                <label for="division">Division</label>
                <select name="division" id="division" required>
                    <option value="">Select Division</option>
                    <option value="A">Division A</option>
                    <option value="B">Division B</option>
                    <option value="C">Division C</option>
                </select>
            </div>

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <div class="location-status" id="locationStatus">
                <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <span>Getting your current location...</span>
            </div>

            <button type="submit">Create Attendance Session</button>
        </form>
    </div>
</div>

<script>
    function updateLocationStatus(text, isError = false) {
        const statusElement = document.getElementById('locationStatus');
        const iconElement = statusElement.querySelector('svg');

        if (isError) {
            statusElement.innerHTML = `
                <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="#f94144" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span style="color: #f94144;">${text}</span>
            `;
        } else {
            iconElement.style.color = '#4cc9f0';
            statusElement.querySelector('span').textContent = text;
        }
    }

    function getLocation() {
        if (navigator.geolocation) {
            updateLocationStatus("Getting your current location...");

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    document.getElementById("latitude").value = position.coords.latitude;
                    document.getElementById("longitude").value = position.coords.longitude;
                    updateLocationStatus(`Location captured: ${position.coords.latitude.toFixed(4)}, ${position.coords.longitude.toFixed(4)}`);
                },
                function (error) {
                    let errorMessage;
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = "Location access denied. Please enable location services.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = "Location information unavailable.";
                            break;
                        case error.TIMEOUT:
                            errorMessage = "Location request timed out.";
                            break;
                        default:
                            errorMessage = "Unknown error occurred while getting location.";
                    }
                    updateLocationStatus(errorMessage, true);
                },
                {enableHighAccuracy: true, timeout: 10000}
            );
        } else {
            updateLocationStatus("Geolocation is not supported by this browser.", true);
        }
    }

    document.getElementById('sessionForm').addEventListener('submit', function (e) {
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;

        if (!latitude || !longitude) {
            alert('Please wait while we get your location. If this persists, check your location permissions.');
            e.preventDefault();
            return false;
        }

        return true;
    });

    window.onload = getLocation;
</script>
</body>
</html>

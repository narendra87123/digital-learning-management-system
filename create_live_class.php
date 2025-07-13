<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $platform = trim($_POST['platform']);
    $join_link = trim($_POST['join_link']);
    $scheduled_at = $_POST['scheduled_at'];
    $faculty_id = $_SESSION['user_id'];

    if (empty($course_id) || empty($title) || empty($join_link) || empty($scheduled_at)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO live_classes (course_id, faculty_id, title, description, platform, join_link, scheduled_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisssss", $course_id, $faculty_id, $title, $description, $platform, $join_link, $scheduled_at);

        if ($stmt->execute()) {
            $success = "Live class created successfully!";
        } else {
            $error = "Error creating live class.";
        }
        $stmt->close();
    }
}

// Fetch courses for this teacher
$faculty_id = $_SESSION['user_id'];
$courses = [];
$stmt = $conn->prepare("SELECT id, name FROM courses WHERE teacher_id = ?");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Live Class</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f8;
            padding: 40px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #2c3e50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1a252f;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #2d89ef;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <a class="back-btn" href="teacher-dashboard.php">← Back to Dashboard</a>
<div class="container">
    <h2>Create Live Class</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Course:</label>
        <select name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Description:</label>
        <textarea name="description"></textarea>

        <label>Platform (Zoom/Meet/etc):</label>
        <input type="text" name="platform">

        <label>Join Link:</label>
        <input type="url" name="join_link" required placeholder="https://zoom.us/...">

        <label>Scheduled At:</label>
        <input type="datetime-local" name="scheduled_at" required>

        <button type="submit">Create Live Class</button>
    </form>
</div>
</body>
</html>

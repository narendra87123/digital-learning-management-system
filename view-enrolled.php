<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

$courses = $conn->query("SELECT * FROM courses WHERE teacher_id = $teacher_id");

$students = [];
if ($course_id > 0) {
    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.email
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        WHERE e.course_id = ?
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Enrolled Students</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #2c3e50;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
            background: rgba(255, 255, 255, 0.96);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        form {
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
        }

        select {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        h3 {
            margin-top: 30px;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        ul li {
            background-color: #f7f7f7;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        ul li i {
            color: #34495e;
            margin-right: 8px;
        }

        a.back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 16px;
            background-color: #34495e;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        a.back-link:hover {
            background-color: #2c3e50;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-users"></i> View Enrolled Students</h2>

    <form method="GET" action="">
        <label><i class="fas fa-book"></i> Select a Course:</label>
        <select name="course_id" onchange="this.form.submit()">
            <option value="">-- Select Course --</option>
            <?php while ($row = $courses->fetch_assoc()): ?>
                <option value="<?= $row['id']; ?>" <?= ($row['id'] == $course_id) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($row['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($course_id > 0): ?>
        <h3><i class="fas fa-user-graduate"></i> Enrolled Students:</h3>
        <?php if ($students->num_rows > 0): ?>
            <ul>
                <?php while ($student = $students->fetch_assoc()): ?>
                    <li>
                        <i class="fas fa-user"></i> <?= htmlspecialchars($student['name']) ?>
                        <br><i class="fas fa-envelope"></i> <?= htmlspecialchars($student['email']) ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No students enrolled yet.</p>
        <?php endif; ?>
    <?php endif; ?>

    <a href="teacher-dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>
</body>
</html>
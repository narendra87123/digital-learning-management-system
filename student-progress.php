<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch enrolled courses
$courses = $conn->query("
    SELECT c.id, c.name 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    WHERE e.student_id = $student_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Progress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            padding: 30px;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #ffffffee;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .course-card {
            background: #f9f9f9;
            padding: 20px;
            border-left: 5px solid #3498db;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .course-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }

        .progress-container {
            width: 100%;
            background: #e0e0e0;
            border-radius: 20px;
            overflow: hidden;
            height: 22px;
        }

        .progress-bar {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(to right, #4caf50, #8bc34a);
            text-align: center;
            line-height: 22px;
            color: white;
            font-size: 13px;
            font-weight: bold;
            transition: width 0.4s ease;
        }

        .back-btn {
            text-align: center;
            margin-top: 30px;
        }

        .back-btn a {
            background-color: #34495e;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .back-btn a:hover {
            background-color: #2c3e50;
        }

        .no-courses {
            text-align: center;
            background: #ffffffbb;
            padding: 20px;
            border-radius: 8px;
            color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-chart-line"></i> Your Course Progress</h2>

    <?php if ($courses->num_rows > 0): ?>
        <?php while ($course = $courses->fetch_assoc()): ?>
            <?php
                $course_id = $course['id'];

                // Total materials
                $total = $conn->query("SELECT COUNT(*) as total FROM materials WHERE course_id = $course_id")->fetch_assoc()['total'];

                // Submissions
                $submitted = $conn->query("
                    SELECT COUNT(*) as submitted 
                    FROM submissions s 
                    JOIN materials m ON s.material_id = m.id 
                    WHERE s.student_id = $student_id AND m.course_id = $course_id
                ")->fetch_assoc()['submitted'];

                $percent = $total > 0 ? round(($submitted / $total) * 100) : 0;
            ?>

            <div class="course-card">
                <h3><i class="fas fa-book-open"></i> <?= htmlspecialchars($course['name']) ?></h3>
                <p><i class="fas fa-tasks"></i> Submitted <?= $submitted ?> of <?= $total ?> assignments (<?= $percent ?>%)</p>
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?= $percent ?>%;"><?= $percent ?>%</div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-courses">
            <i class="fas fa-exclamation-circle"></i> You are not enrolled in any courses yet.
        </div>
    <?php endif; ?>

    <div class="back-btn">
        <a href="student-dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>
</body>
</html>
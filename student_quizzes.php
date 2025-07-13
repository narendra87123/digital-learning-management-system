<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id']);

$quizzes = $conn->query("SELECT * FROM quizzes WHERE course_id = $course_id ORDER BY deadline DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quizzes</title>
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
            max-width: 850px;
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

        .quiz-list {
            list-style: none;
            padding: 0;
        }

        .quiz-item {
            background: #f9f9f9;
            padding: 20px;
            border-left: 6px solid #3498db;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .quiz-info {
            flex: 1;
            min-width: 250px;
        }

        .quiz-info strong {
            font-size: 18px;
            color: #34495e;
        }

        .quiz-info p {
            margin: 6px 0;
            color: #555;
        }

        .quiz-actions a {
            background-color: #2ecc71;
            color: white;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .quiz-actions a:hover {
            background-color: #27ae60;
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

        .no-quizzes {
            text-align: center;
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-clipboard-list"></i> Quizzes for Course ID: <?= htmlspecialchars($course_id) ?></h2>

    <?php if ($quizzes->num_rows > 0): ?>
        <ul class="quiz-list">
            <?php while ($q = $quizzes->fetch_assoc()): ?>
                <li class="quiz-item">
                    <div class="quiz-info">
                        <strong><i class="fas fa-file-alt"></i> <?= htmlspecialchars($q['title']) ?></strong>
                        <p><i class="fas fa-hourglass-end"></i> Deadline: <?= htmlspecialchars($q['deadline']) ?></p>
                    </div>
                    <div class="quiz-actions">
                        <a href="attempt_quiz.php?quiz_id=<?= $q['id'] ?>"><i class="fas fa-pen"></i> Attempt</a>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p class="no-quizzes"><i class="fas fa-circle-exclamation"></i> No quizzes available for this course yet.</p>
    <?php endif; ?>

    <div class="back-btn">
        <a href="student-dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>
</body>
</html>
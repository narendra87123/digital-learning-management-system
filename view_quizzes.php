<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Validate course belongs to teacher
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $course_id, $teacher_id);
$stmt->execute();
$course_result = $stmt->get_result();

if ($course_result->num_rows === 0) {
    die("Invalid course or unauthorized access.");
}

// Get course title
$course = $course_result->fetch_assoc();
$course_title = $course['title'] ?? 'Unknown Course';

// Get quizzes for the course
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$quizzes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quizzes | Digital LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 850px;
            margin: 70px auto;
            background: rgba(255, 255, 255, 0.96);
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.15);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 35px;
            font-size: 1.8rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            background: #f9f9f9;
            margin: 15px 0;
            padding: 18px 22px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid #7f7fd5;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        li:hover {
            transform: translateY(-2px);
            background-color: #f0f7ff;
        }

        li i {
            margin-right: 10px;
            color: #2c3e50;
        }

        .quiz-title {
            font-weight: bold;
            color: #34495e;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
        }

        a.view-link {
            background-color: #34495e;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: background 0.3s ease;
        }

        a.view-link:hover {
            background-color: #1a252f;
        }

        .back-button {
            display: inline-block;
            margin-top: 25px;
            background-color: #354f6b;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.95rem;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background-color: #2c3e50;
        }

        .no-quiz {
            text-align: center;
            color: #555;
            font-style: italic;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-book-open"></i> Quizzes for: <?= htmlspecialchars($course_title) ?></h2>

    <?php if ($quizzes->num_rows > 0): ?>
        <ul>
            <?php while ($row = $quizzes->fetch_assoc()): ?>
                <li>
                    <div class="quiz-title">
                        <i class="fas fa-clipboard-question"></i>
                        <?= htmlspecialchars($row['title']) ?>
                    </div>
                    <a class="view-link" href="view-quiz-submissions.php?quiz_id=<?= $row['id'] ?>">
                        <i class="fas fa-eye"></i> View Submissions
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p class="no-quiz"><i class="fas fa-info-circle"></i> No quizzes found for this course.</p>
    <?php endif; ?>

    <a class="back-button" href="teacher-dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>
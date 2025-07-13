<?php
session_start();
include 'db.php';

// Check if user is a logged-in teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Check if quiz_id is passed
if (!isset($_GET['quiz_id'])) {
    echo "Quiz ID not provided.";
    exit();
}

$quiz_id = intval($_GET['quiz_id']);

// Step 1: Verify the quiz belongs to a course owned by the logged-in teacher
$stmt = $conn->prepare("
    SELECT q.*, c.name AS course_name 
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE q.id = ? AND c.teacher_id = ?
");
$stmt->bind_param("ii", $quiz_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Quiz not found or you are not authorized to view it.";
    exit();
}

$quiz = $result->fetch_assoc();

// Step 2: Get quiz submissions
$stmt = $conn->prepare("
    SELECT s.*, u.name AS student_name
    FROM quiz_submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.quiz_id = ?
");
$stmt->bind_param("i", $quiz_id);  // Fixed from "I" to "i"
$stmt->execute();
$submissions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Submissions</title>
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
            max-width: 900px;
            margin: 60px auto;
            background: rgba(255, 255, 255, 0.96);
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        p.course-info {
            text-align: center;
            font-size: 1rem;
            color: #555;
            margin-bottom: 30px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        th, td {
            padding: 12px 16px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #7f7fd5;
            color: white;
        }

        td a.download-link {
            color: #2980b9;
            text-decoration: none;
        }

        td a.download-link:hover {
            text-decoration: underline;
        }

        .no-submissions {
            text-align: center;
            margin: 30px 0;
            font-style: italic;
            color: #555;
        }

        .back-button {
            display: inline-block;
            margin-top: 30px;
            background-color: #354f6b;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.95rem;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background-color: #2c3e50;
        }

        .icon {
            margin-right: 8px;
            color: #34495e;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-file-alt icon"></i> Submissions for Quiz: <?= htmlspecialchars($quiz['title']) ?></h2>
    <p class="course-info"><i class="fas fa-book icon"></i> Course: <?= htmlspecialchars($quiz['course_name']) ?></p>

    <?php if ($submissions->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-user icon"></i>Student Name</th>
                    <th><i class="fas fa-clock icon"></i>Submitted At</th>
                    <th><i class="fas fa-file-download icon"></i>File</th>
                    <th><i class="fas fa-star icon"></i>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $submissions->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['submitted_at']) ?></td>
                        <td>
                            <?php if (!empty($row['file_path'])): ?>
                                <a class="download-link" href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= $row['score'] !== null ? htmlspecialchars($row['score']) : 'Not graded' ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-submissions"><i class="fas fa-info-circle"></i> No submissions yet for this quiz.</p>
    <?php endif; ?>

    <a class="back-button" href="teacher-dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>
</body>
</html>
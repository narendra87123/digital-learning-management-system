<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT s.*, m.title AS material_title, c.name AS course_name
    FROM submissions s
    JOIN materials m ON s.material_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE s.student_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Grades & Feedback</title>
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

        .result-card {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #3498db;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .result-card strong {
            color: #2c3e50;
        }

        .result-card i {
            color: #2980b9;
            margin-right: 6px;
        }

        .grade {
            font-weight: bold;
            color: green;
        }

        .feedback {
            margin-top: 8px;
            font-style: italic;
            background-color: #ecf0f1;
            padding: 10px;
            border-radius: 6px;
            color: #2c3e50;
        }

        a.download-link {
            color: #2980b9;
            text-decoration: none;
        }

        a.download-link:hover {
            text-decoration: underline;
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

        .empty-msg {
            text-align: center;
            color: #555;
            padding: 20px;
            background: #ffffffaa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-graduation-cap"></i> My Grades & Feedback</h2>

    <?php if ($results->num_rows > 0): ?>
        <?php while ($row = $results->fetch_assoc()): ?>
            <div class="result-card">
                <p><i class="fas fa-book"></i> <strong>Course:</strong> <?= htmlspecialchars($row['course_name']) ?></p>
                <p><i class="fas fa-file-alt"></i> <strong>Assignment:</strong> <?= htmlspecialchars($row['material_title']) ?></p>
                <p><i class="fas fa-clock"></i> <strong>Submitted on:</strong> <?= date("d M Y, h:i A", strtotime($row['submitted_at'])) ?></p>
                <p><i class="fas fa-download"></i> <strong>Download:</strong> <a class="download-link" href="<?= $row['file_path'] ?>" download>📎 Your Submission</a></p>
                <p><i class="fas fa-star"></i> <strong>Grade:</strong>
                    <span class="grade"><?= $row['grade'] ? htmlspecialchars($row['grade']) : 'Pending' ?></span>
                </p>
                <?php if (!empty($row['feedback'])): ?>
                    <div class="feedback">
                        <i class="fas fa-comment-dots"></i> <?= nl2br(htmlspecialchars($row['feedback'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-msg">
            <i class="fas fa-inbox"></i> You haven't submitted any assignments yet.
        </div>
    <?php endif; ?>

    <div class="back-btn">
        <a href="student-dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>
</body>
</html>
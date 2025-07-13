<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$quiz_id = intval($_GET['quiz_id'] ?? 0);

// Fetch quiz
$quiz_stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$quiz_stmt->bind_param("i", $quiz_id);
$quiz_stmt->execute();
$quiz = $quiz_stmt->get_result()->fetch_assoc();

if (!$quiz) {
    die("Quiz not found.");
}

// Check deadline
if (strtotime($quiz['deadline']) < time()) {
    die("The deadline for this quiz has passed.");
}

// ✅ Check if student already submitted
$check = $conn->prepare("SELECT id, score FROM quiz_submissions WHERE quiz_id = ? AND student_id = ?");
$check->bind_param("ii", $quiz_id, $student_id);
$check->execute();
$submitted = $check->get_result()->fetch_assoc();

if ($submitted) {
    echo "<h3>You have already submitted this quiz.</h3>";
    echo "<p>Your score: <strong>" . htmlspecialchars($submitted['score']) . "</strong></p>";
    echo "<a href='student-dashboard.php'>← Back to Dashboard</a>";
    exit();
}

// Fetch questions
$questions = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO quiz_submissions (quiz_id, student_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $quiz_id, $student_id);
    $stmt->execute();
    $submission_id = $stmt->insert_id;

    $score = 0;

    foreach ($_POST['answers'] as $question_id => $answer) {
        $question_id = intval($question_id);
        $answer = strtoupper(substr($answer, 0, 1)); // sanitize

        $q = $conn->query("SELECT correct_option FROM quiz_questions WHERE id = $question_id")->fetch_assoc();
        if ($q && $q['correct_option'] === $answer) {
            $score++;
        }

        $stmt2 = $conn->prepare("INSERT INTO quiz_answers (submission_id, question_id, selected_option) VALUES (?, ?, ?)");
        $stmt2->bind_param("iis", $submission_id, $question_id, $answer);
        $stmt2->execute();
    }

    $update_score = $conn->prepare("UPDATE quiz_submissions SET score = ? WHERE id = ?");
    $update_score->bind_param("ii", $score, $submission_id);
    $update_score->execute();

    echo "<h3>Quiz submitted successfully!</h3>";
    echo "<p>Your score: <strong>$score</strong></p>";
    echo "<a href='student-dashboard.php'>← Back to Dashboard</a>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attempt Quiz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            margin: 0;
            padding: 0;
            color: #2c3e50;
        }

        .container {
            max-width: 800px;
            background: #fff;
            margin: 40px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #34495e;
        }

        p.description {
            font-size: 16px;
            line-height: 1.5;
            background: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
        }

        .quiz-question {
            background: #f9f9f9;
            border-left: 5px solid #3498db;
            padding: 20px;
            margin-top: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .quiz-question p {
            font-weight: bold;
            margin-bottom: 12px;
        }

        label {
            display: block;
            padding: 10px;
            background: #ecf0f1;
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: 0.2s ease-in-out;
        }

        label:hover {
            background-color: #d0e6ff;
        }

        input[type="radio"] {
            margin-right: 10px;
            transform: scale(1.2);
        }

        input[type="submit"] {
            background-color: #27ae60;
            color: white;
            font-size: 16px;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: block;
            margin: 30px auto 0;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #1e8449;
        }

        a.back {
            display: inline-block;
            margin-top: 20px;
            color: #2980b9;
            text-decoration: none;
        }

        a.back:hover {
            text-decoration: underline;
        }

        .icon {
            margin-right: 8px;
            color: #3498db;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fa-solid fa-pen-to-square icon"></i><?php echo htmlspecialchars($quiz['title']); ?></h2>
    <p class="description"><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>

    <form method="POST">
        <?php while ($q = $questions->fetch_assoc()): ?>
            <div class="quiz-question">
                <p><i class="fa-solid fa-circle-question icon"></i><?php echo htmlspecialchars($q['question']); ?></p>
                <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="A" required> A) <?php echo htmlspecialchars($q['option_a']); ?></label>
                <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="B"> B) <?php echo htmlspecialchars($q['option_b']); ?></label>
                <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="C"> C) <?php echo htmlspecialchars($q['option_c']); ?></label>
                <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="D"> D) <?php echo htmlspecialchars($q['option_d']); ?></label>
            </div>
        <?php endwhile; ?>

        <input type="submit" value="Submit Quiz">
    </form>
    <a href="student-dashboard.php" class="back"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
</div>
</body>
</html>
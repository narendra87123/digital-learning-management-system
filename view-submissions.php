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
    // Insert submission
    $stmt = $conn->prepare("INSERT INTO quiz_submissions (quiz_id, student_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $quiz_id, $student_id);
    $stmt->execute();
    $submission_id = $stmt->insert_id;

    $score = 0;

    foreach ($_POST['answers'] as $question_id => $answer) {
        $question_id = intval($question_id);
        $answer = strtoupper(substr($answer, 0, 1)); // Sanitize answer

        // Get correct answer
        $q = $conn->query("SELECT correct_option FROM quiz_questions WHERE id = $question_id")->fetch_assoc();
        if ($q && $q['correct_option'] === $answer) {
            $score++;
        }

        // Save student answer
        $stmt2 = $conn->prepare("INSERT INTO quiz_answers (submission_id, question_id, selected_option) VALUES (?, ?, ?)");
        $stmt2->bind_param("iis", $submission_id, $question_id, $answer);
        $stmt2->execute();
    }

    // Update score
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
    <p><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>

    <form method="POST">
        <?php while ($q = $questions->fetch_assoc()): ?>
            <div class="quiz-question">
                <p><strong><?php echo htmlspecialchars($q['question']); ?></strong></p>
                <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="A" required> A) <?php echo htmlspecialchars($q['option_a']); ?></label><br>
                <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="B"> B) <?php echo htmlspecialchars($q['option_b']); ?></label><br>
                <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="C"> C) <?php echo htmlspecialchars($q['option_c']); ?></label><br>
                <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="D"> D) <?php echo htmlspecialchars($q['option_d']); ?></label><br><br>
            </div>
        <?php endwhile; ?>

        <input type="submit" value="Submit Quiz">
    </form>
</div>
</body>
</html>
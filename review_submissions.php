<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT s.*, st.name as student_name
    FROM submissions s
    JOIN students st ON s.student_id = st.id
    JOIN courses c ON s.course_id = c.id
    WHERE s.course_id = ? AND c.teacher_id = ?
");
$stmt->bind_param("ii", $course_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head><title>Review Submissions</title></head>
<body>
<h2>Student Submissions for Course #<?= $course_id ?></h2>
<?php while ($row = $result->fetch_assoc()) { ?>
    <div>
        <p><strong>Student:</strong> <?= htmlspecialchars($row['student_name']) ?></p>
        <p><strong>Answer:</strong> <?= nl2br(htmlspecialchars($row['answer_text'])) ?></p>
        <form method="POST" action="reply_submission.php">
            <input type="hidden" name="submission_id" value="<?= $row['id'] ?>">
            <textarea name="teacher_reply" rows="3" cols="50" placeholder="Write feedback..."></textarea><br>
            <input type="submit" value="Submit Reply">
        </form>
        <hr>
    </div>
<?php } ?>
</body>
</html>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];
$question_text = trim($_POST['question_text']);

if (!empty($course_id) && !empty($question_text)) {
    $stmt = $conn->prepare("INSERT INTO questions (course_id, student_id, question_text, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $course_id, $student_id, $question_text);
    $stmt->execute();
    $stmt->close();
}

header("Location: student-dashboard.php");
exit();
?>

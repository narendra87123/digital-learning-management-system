<?php
include 'db_connect.php'; // your DB connection file

$course_id = $_POST['course_id'];
$student_id = $_POST['student_id'];
$question_text = $_POST['question_text'];

$sql = "INSERT INTO questions (course_id, student_id, question_text) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $course_id, $student_id, $question_text);

if ($stmt->execute()) {
    echo "Question posted successfully.";
} else {
    echo "Error: " . $conn->error;
}
?>

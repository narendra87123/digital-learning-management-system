<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $assignment1 = $_POST['assignment1'];
    $assignment2 = $_POST['assignment2'];
    $mid_exam = $_POST['mid_exam'];
    $lab_exam = $_POST['lab_exam'];
    $faculty_id = $_SESSION['user_id'];

    $total = $assignment1 + $assignment2 + $mid_exam + $lab_exam;

    $stmt = $conn->prepare("INSERT INTO marks (student_id, course_id, faculty_id, assignment1, assignment2, mid_exam, lab_exam, total, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiiiiii", $student_id, $course_id, $faculty_id, $assignment1, $assignment2, $mid_exam, $lab_exam, $total);

    if ($stmt->execute()) {
        echo "Marks uploaded successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

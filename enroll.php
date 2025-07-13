<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id'])) {
    $student_id = $_SESSION['user_id'];
    $course_id = intval($_POST['course_id']);

    $check = $conn->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
    $check->bind_param("ii", $student_id, $course_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        // Not enrolled yet, insert enrollment
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $student_id, $course_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Enrolled successfully.";
        } else {
            $_SESSION['message'] = "Enrollment failed.";
        }
    } else {
        // Already enrolled
        $_SESSION['message'] = "Already enrolled in this course.";
    }

    header("Location: student-dashboard.php");
    exit();
}
?>

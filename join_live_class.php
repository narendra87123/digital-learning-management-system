<?php
session_start();
include 'db.php';

// Only allow students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized access.");
}

$studentId = $_SESSION['user_id'];
$liveClassId = $_GET['class_id']; // This comes from the link

// Check if attendance already exists
$check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND live_class_id = ?");
$check->bind_param("ii", $studentId, $liveClassId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    // Insert attendance
    $stmt = $conn->prepare("INSERT INTO attendance (student_id, live_class_id, attended_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $studentId, $liveClassId);
    $stmt->execute();
}

// Get the join link
$stmt = $conn->prepare("SELECT join_link FROM live_classes WHERE id = ?");
$stmt->bind_param("i", $liveClassId);
$stmt->execute();
$res = $stmt->get_result();
$class = $res->fetch_assoc();

if ($class) {
    // Redirect to Zoom/Meet/etc.
    header("Location: " . $class['join_link']);
    exit();
} else {
    echo "Class not found.";
}
?>

<?php
session_start();
include 'db.php';

$student_id = $_SESSION['user']['id'];
$live_class_id = $_GET['id'];

// Check if already marked
$check = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND live_class_id = ?");
$check->bind_param("ii", $student_id, $live_class_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO attendance (student_id, live_class_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $student_id, $live_class_id);
    $stmt->execute();
}

// Redirect to actual join link
$join = $conn->query("SELECT join_link FROM live_classes WHERE id = $live_class_id")->fetch_assoc();
header("Location: " . $join['join_link']);
exit;

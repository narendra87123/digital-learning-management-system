<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$facultyId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT title, join_link FROM live_classes WHERE faculty_id = ?");
$stmt->bind_param("i", $facultyId);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = [
        'title' => $row['title'],
        'link' => $row['join_link'] // use correct column name here
    ];
}

echo json_encode($classes);
$stmt->close();
$conn->close();
?>

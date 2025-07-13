<?php
include 'db_connect.php';

$question_id = $_POST['question_id'];
$user_id = $_POST['user_id'];
$role = $_POST['role'];
$reply_text = $_POST['reply_text'];

$sql = "INSERT INTO replies (question_id, user_id, role, reply_text) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $question_id, $user_id, $role, $reply_text);

if ($stmt->execute()) {
    echo "Reply posted successfully.";
} else {
    echo "Error: " . $conn->error;
}
?>

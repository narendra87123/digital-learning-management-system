<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submission_id'], $_POST['teacher_reply'])) {
    $submission_id = intval($_POST['submission_id']);
    $reply = trim($_POST['teacher_reply']);

    $stmt = $conn->prepare("UPDATE submissions SET teacher_reply = ? WHERE id = ?");
    $stmt->bind_param("si", $reply, $submission_id);
    $stmt->execute();
}

header("Location: {$_SERVER['HTTP_REFERER']}");
exit();

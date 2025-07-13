<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get current picture
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($pic);
$stmt->fetch();
$stmt->close();

if ($pic && file_exists("uploads/" . $pic)) {
    unlink("uploads/" . $pic);
}

$stmt = $conn->prepare("UPDATE users SET profile_picture = NULL, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

header("Location: profile.php");
exit();
?>

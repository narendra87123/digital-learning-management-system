<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['new_picture']) && $_FILES['new_picture']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['new_picture']['tmp_name'];
    $fileName = basename($_FILES['new_picture']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExt, $allowed)) {
        $newName = uniqid("profile_") . "." . $fileExt;
        $uploadDir = "uploads/";
        $uploadPath = $uploadDir . $newName;

        if (move_uploaded_file($fileTmp, $uploadPath)) {
            // Delete old picture
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($oldPic);
            $stmt->fetch();
            $stmt->close();

            if ($oldPic && file_exists("uploads/" . $oldPic)) {
                unlink("uploads/" . $oldPic);
            }

            // Update DB
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $newName, $user_id);
            $stmt->execute();
        }
    }
}

header("Location: profile.php");
exit();
?>

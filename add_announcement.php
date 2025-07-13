<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$msg = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $content);
        if ($stmt->execute()) {
            $msg = "Announcement posted successfully!";
            $success = true;
        } else {
            $msg = "Failed to post announcement.";
        }
    } else {
        $msg = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Announcement | Digital LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
            background: rgba(255, 255, 255, 0.96);
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .title-heading {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.8rem;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #2c3e50;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }

        textarea {
            resize: vertical;
        }

        .submit-btn {
            display: inline-block;
            background-color: #2c3e50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 25px;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #1a252f;
        }

        .msg {
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: bold;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .back-button {
            display: block;
            margin: 30px auto 0;
            text-align: center;
            background-color: #354f6b;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1rem;
            transition: background 0.3s ease;
            width: fit-content;
        }

        .back-button:hover {
            background-color: #2c3e50;
        }

        .icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="title-heading">
        <i class="fas fa-bell icon"></i> Post New Announcement
    </div>

    <?php if ($msg): ?>
        <div class="msg <?= $success ? 'success' : 'error' ?>">
            <i class="fas <?= $success ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <label><i class="fas fa-heading icon"></i>Title:</label>
        <input type="text" name="title" required>

        <label><i class="fas fa-align-left icon"></i>Content:</label>
        <textarea name="content" rows="6" required></textarea>

        <button class="submit-btn" type="submit"><i class="fas fa-paper-plane icon"></i>Post Announcement</button>
    </form>

    <a class="back-button" href="teacher-dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>
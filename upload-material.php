<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id'] ?? 0);

$check = $conn->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$check->bind_param("ii", $course_id, $teacher_id);
$check->execute();
$course = $check->get_result()->fetch_assoc();

if (!$course) {
    die("❌ Unauthorized access to this course.");
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'] ?? null;

    $file_path = "";
    if (!empty($_FILES['material_file']['name']) && $_FILES['material_file']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $filename = basename($_FILES["material_file"]["name"]);
        $file_path = $upload_dir . $filename;

        if (!move_uploaded_file($_FILES["material_file"]["tmp_name"], $file_path)) {
            $msg = "❌ File upload failed.";
            $file_path = "";
        }
    }

    $stmt = $conn->prepare("INSERT INTO materials (course_id, title, description, file_path, deadline, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $course_id, $title, $description, $file_path, $deadline);

    if ($stmt->execute()) {
        $msg = "✅ Material uploaded successfully!";
    } else {
        $msg = "❌ Failed to upload material.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Course Material</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #2c3e50;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
            font-size: 1rem;
        }

        input[type="text"],
        textarea,
        input[type="datetime-local"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }

        textarea {
            resize: vertical;
        }

        input[type="submit"] {
            background-color: #2c3e50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #1a252f;
        }

        .message {
            font-weight: bold;
            margin-bottom: 20px;
            color: green;
            text-align: center;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 16px;
            background-color: #34495e;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            background-color: #2c3e50;
        }

        .icon-label i {
            margin-right: 8px;
            color: #2c3e50;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-cloud-upload-alt"></i> Upload Material to "<?php echo htmlspecialchars($course['name']); ?>"</h2>

    <form method="POST" enctype="multipart/form-data">
        <?php if ($msg): ?>
            <p class="message"><?php echo $msg; ?></p>
        <?php endif; ?>

        <div class="form-group">
            <label class="icon-label"><i class="fas fa-heading"></i> Title:</label>
            <input type="text" name="title" required>
        </div>

        <div class="form-group">
            <label class="icon-label"><i class="fas fa-align-left"></i> Description:</label>
            <textarea name="description" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label class="icon-label"><i class="fas fa-paperclip"></i> Attach File (PDF/DOC/ZIP etc):</label>
            <input type="file" name="material_file">
        </div>

        <div class="form-group">
            <label class="icon-label"><i class="fas fa-calendar-alt"></i> Deadline:</label>
            <input type="datetime-local" name="deadline" required>
        </div>

        <input type="submit" value="📤 Upload Material">
        <br><br>
        <a href="teacher-dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </form>
</div>

</body>
</html>
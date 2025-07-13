<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id'] ?? 0);

$course = $conn->query("SELECT * FROM courses WHERE id = $course_id")->fetch_assoc();
if (!$course) die("Invalid course.");

$materials = $conn->query("SELECT * FROM materials WHERE course_id = $course_id");

$msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['material_id'])) {
    $material_id = intval($_POST['material_id']);
    $filename = "";

    $deadlineStmt = $conn->prepare("SELECT deadline FROM materials WHERE id = ?");
    $deadlineStmt->bind_param("i", $material_id);
    $deadlineStmt->execute();
    $deadlineResult = $deadlineStmt->get_result()->fetch_assoc();

    $isDeadlineOver = $deadlineResult && strtotime($deadlineResult['deadline']) < time();

    if ($isDeadlineOver) {
        $msg = "⛔ Deadline has passed. Submission not allowed.";
    } else {
        if (isset($_FILES['response_file']) && $_FILES['response_file']['error'] == 0) {
            $target_dir = "submissions/";
            if (!is_dir($target_dir)) mkdir($target_dir);

            $filename = basename($_FILES["response_file"]["name"]);
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($_FILES["response_file"]["tmp_name"], $target_file)) {
                $checkStmt = $conn->prepare("SELECT id FROM submissions WHERE student_id = ? AND material_id = ?");
                $checkStmt->bind_param("ii", $student_id, $material_id);
                $checkStmt->execute();
                $checkStmt->store_result();

                if ($checkStmt->num_rows > 0) {
                    $updateStmt = $conn->prepare("UPDATE submissions SET file_path = ?, submitted_at = NOW() WHERE student_id = ? AND material_id = ?");
                    $updateStmt->bind_param("sii", $filename, $student_id, $material_id);
                    $updateStmt->execute();
                    $msg = "✅ Submission updated successfully!";
                } else {
                    $stmt = $conn->prepare("INSERT INTO submissions (material_id, student_id, file_path, submitted_at) VALUES (?, ?, ?, NOW())");
                    $stmt->bind_param("iis", $material_id, $student_id, $filename);
                    $stmt->execute();
                    $msg = "✅ Submitted successfully!";
                }
            } else {
                $msg = "❌ Failed to upload file.";
            }
        } else {
            $msg = "❌ No file uploaded.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Assignments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            padding: 30px;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #ffffffee;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #2c3e50;
            background-color: #eaf4fe;
            border-left: 5px solid #3498db;
            border-radius: 6px;
        }

        .material {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-left: 5px solid #2980b9;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .material h4 {
            margin: 0 0 10px;
            color: #34495e;
        }

        .material p {
            margin: 6px 0;
        }

        .submitted-label {
            color: green;
            font-weight: bold;
            margin-top: 8px;
        }

        .deadline-label {
            color: #d35400;
            font-weight: bold;
        }

        form {
            margin-top: 12px;
        }

        .material label {
            display: inline-block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #2c3e50;
        }

        input[type="file"] {
            margin: 5px 0 10px;
            width: 100%;
            padding: 5px;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: white;
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #2c80b4;
        }

        a.download-link {
            display: inline-block;
            margin-top: 5px;
            color: #2980b9;
            text-decoration: none;
        }

        a.download-link:hover {
            text-decoration: underline;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 30px;
        }

        .back-btn a {
            background-color: #34495e;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .back-btn a:hover {
            background-color: #2c3e50;
        }

        .icon {
            margin-right: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-book-open"></i> Assignments for "<?php echo htmlspecialchars($course['name']); ?>"</h2>

    <?php if ($msg): ?>
        <div class="message"><?php echo $msg; ?></div>
    <?php endif; ?>

    <?php
    $existing = $conn->prepare("SELECT material_id FROM submissions WHERE student_id = ?");
    $existing->bind_param("i", $student_id);
    $existing->execute();
    $result = $existing->get_result();

    $submitted_ids = [];
    while ($row = $result->fetch_assoc()) {
        $submitted_ids[] = $row['material_id'];
    }
    ?>

    <?php while ($mat = $materials->fetch_assoc()):
        $deadline = $mat['deadline'];
        $isSubmitted = in_array($mat['id'], $submitted_ids);
        $isDeadlineOver = $deadline && strtotime($deadline) < time();
    ?>
        <div class="material">
            <!-- Title -->
            <h4><i class="fas fa-file-alt icon"></i> <?= htmlspecialchars($mat['title']); ?></h4>

            <!-- Description -->
            <p><?= nl2br(htmlspecialchars($mat['description'])); ?></p>

            <!-- File download -->
            <?php if (!empty($mat['file_path'])): ?>
                <p>
                    <strong><i class="fas fa-paperclip icon"></i> Attachment:</strong>
                    <a class="download-link" href="<?= $mat['file_path']; ?>" download>
                        Download File
                    </a>
                </p>
            <?php endif; ?>

            <!-- Deadline -->
            <?php if (!empty($deadline)): ?>
                <p class="deadline-label">
                    <i class="fas fa-calendar-alt icon"></i>
                    <strong>Deadline:</strong> <?= date("d M Y, h:i A", strtotime($deadline)); ?>
                </p>
            <?php endif; ?>

            <!-- Submission Status & Form -->
            <?php if ($isDeadlineOver): ?>
                <p class="submitted-label" style="color: red;">
                    <i class="fas fa-clock icon"></i> Deadline has passed. Submissions are closed.
                </p>
            <?php elseif ($isSubmitted): ?>
                <p class="submitted-label">
                    <i class="fas fa-check-circle icon"></i> Already submitted. You can update before the deadline.
                </p>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="material_id" value="<?= $mat['id']; ?>">

                    <label><i class="fas fa-upload icon"></i> Update File:</label><br>
                    <input type="file" name="response_file" required><br>

                    <input type="submit" value="Update Submission">
                </form>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="material_id" value="<?= $mat['id']; ?>">

                    <label><i class="fas fa-upload icon"></i> Submit Your File:</label><br>
                    <input type="file" name="response_file" required><br>

                    <input type="submit" value="Submit">
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <div class="back-btn">
        <a href="student-dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>
</body>
</html>
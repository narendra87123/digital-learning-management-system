<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Add Course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    $course_name = trim($_POST['course_name']);
    if (!empty($course_name)) {
        $stmt = $conn->prepare("INSERT INTO courses (name, teacher_id) VALUES (?, ?)");
        $stmt->bind_param("si", $course_name, $teacher_id);
        $stmt->execute();
    }
}

// Delete Course
if (isset($_GET['delete_course'])) {
    $delete_id = intval($_GET['delete_course']);
    $conn->query("DELETE FROM courses WHERE id = $delete_id AND teacher_id = $teacher_id");
}

// Upload Material
$uploadMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_material'])) {
    $course_id = intval($_POST['course_id']);
    $title = trim($_POST['title']);
    $file = $_FILES['material'];

    $uploadDir = "uploads/materials/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = basename($file['name']);
    $filePath = $uploadDir . time() . '_' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $stmt = $conn->prepare("INSERT INTO course_materials (course_id, title, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $course_id, $title, $filePath);
        $stmt->execute();
        $uploadMessage = "Material uploaded successfully.";
    } else {
        $uploadMessage = "File upload failed.";
    }
}

$courses = $conn->query("SELECT * FROM courses WHERE teacher_id = $teacher_id");
$courseQuery = $conn->query("SELECT * FROM courses WHERE teacher_id = $teacher_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard | Digital LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* [same CSS as your code, unchanged for brevity] */

        
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            min-height: 100vh;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .sidebar {
            width: 240px;
            background-color: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px 0;
        }

        .sidebar h2 {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .sidebar a {
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s;
            font-weight: 500;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #34495e;
        }

        .main-content {
            flex: 1;
            padding: 40px;
            background-color: #ffffffdd;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        h3 {
            margin-top: 0;
            color: #2c3e50;
        }

        form input[type="text"],
        form input[type="submit"],
        form select {
            padding: 10px;
            margin: 10px 10px 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        form input[type="submit"] {
            background-color: #27ae60;
            color: white;
            cursor: pointer;
        }

        .course-card {
            background-color: #f8f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .course-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .action-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 4px 12px;
            border-radius: 5px;
            color: white;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .btn.primary { background-color: #3498db; }
        .btn.success { background-color: #2ecc71; }
        .btn.danger  { background-color: #e74c3c; }
        .btn.warning { background-color: #f39c12; }
        .btn.secondary { background-color: #7f8c8d; }

        .footer {
            margin-top: 40px;
        }

        .footer a {
            margin-right: 15px;
            color: #2980b9;
            text-decoration: none;
            font-weight: 500;
        }

        .welcome {
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 1.1rem;
            color: #333;
        }
.welcome-box {
    background-color: #ecf0f1;
    padding: 15px 20px;
    border-left: 5px solid #2980b9;
    border-radius: 6px;
    font-size: 1.1rem;
    margin-bottom: 20px;
    color: #2c3e50;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
        ul { padding-left: 20px; }
        ul li { margin-bottom: 12px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-chalkboard-teacher"></i> Faculty</h2>
    <a href="#" class="tab-link active" data-tab="dashboard"><i class="fas fa-home"></i> Dashboard</a>
    <a href="#" class="tab-link" data-tab="courses"><i class="fas fa-book"></i> My Courses</a>
    <a href="#" class="tab-link" data-tab="quizzes"><i class="fas fa-question-circle"></i> Quizzes</a>
    <a href="#" class="tab-link" data-tab="announcements"><i class="fas fa-bullhorn"></i> Announcements</a>
    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
    <a href="#" class="tab-link" data-tab="liveclasses"><i class="fas fa-video"></i> Live Classes</a>
    <a href="upload_marks.php"><i class="fas fa-chart-line"></i> Upload Marks</a>
    <a href="index.php"><i class="fas fa-arrow-left"></i> Home</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <!-- Dashboard -->
    <div class="section active" id="dashboard">
        <div class="welcome-box">
    Welcome, <strong><?= htmlspecialchars($name); ?></strong>! You are logged in as <strong>Faculty</strong>.
</div>
<p>Use the side menu to manage your courses, quizzes, and announcements.</p>

    </div>

    <!-- Courses -->
    <div class="section" id="courses">
        <h3><i class="fas fa-plus-circle"></i> Add New Course</h3>
        <form method="POST">
            <input type="text" name="course_name" placeholder="Course Name..." required>
            <input type="submit" name="add_course" value="Add Course">
        </form>

        <h3><i class="fas fa-list"></i> Your Courses</h3>
        <?php $courses->data_seek(0); while ($row = $courses->fetch_assoc()) { ?>
            <div class="course-card">
                <div class="course-title"><?= htmlspecialchars($row['name']); ?></div>
                <div class="action-bar">
                    <a href="?delete_course=<?= $row['id']; ?>" class="btn danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i> Delete</a>
                    <a href="upload-material.php?course_id=<?= $row['id']; ?>" class="btn primary"><i class="fas fa-upload"></i> Upload Assignment</a>
                    <a href="view-submissions.php?course_id=<?= $row['id']; ?>" class="btn warning"><i class="fas fa-file-alt"></i> View Submissions</a>
                    <a href="view-enrolled.php?course_id=<?= $row['id']; ?>" class="btn secondary"><i class="fas fa-users"></i> View Students</a>
                </div>
            </div>
        <?php } ?>

        <!-- Upload Material -->
        <div style="margin-top: 30px; border: 1px solid #ccc; padding: 20px;">
            <h3>Upload Course Material</h3>
            <form method="POST" enctype="multipart/form-data">
                <label>Select Course:</label><br>
                <select name="course_id" required>
                    <?php while ($row = $courseQuery->fetch_assoc()) { ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php } ?>
                </select><br><br>

                <label>Material Title:</label><br>
                <input type="text" name="title" required><br><br>

                <label>Upload File:</label><br>
                <input type="file" name="material" required><br><br>

                <input type="submit" name="upload_material" value="Upload">
            </form>
            <?php if ($uploadMessage) echo "<p style='color:green;'>$uploadMessage</p>"; ?>
        </div>
    </div>

    <!-- Quizzes -->
    <div class="section" id="quizzes">
        <h3><i class="fas fa-question-circle"></i> Manage Quizzes</h3>
        <ul>
            <?php
            $courses = $conn->query("SELECT * FROM courses WHERE teacher_id = $teacher_id");
            while ($row = $courses->fetch_assoc()) {
                echo "<li><strong>" . htmlspecialchars($row['name']) . "</strong> - 
                    <a href='upload_quiz.php?course_id={$row['id']}' class='btn success'><i class='fas fa-plus'></i> Add Quiz</a>
                    <a href='view_quizzes.php?course_id={$row['id']}' class='btn warning'><i class='fas fa-eye'></i> View Quizzes</a></li>";
            }
            ?>
        </ul>
    </div>

    <!-- Announcements -->
    <div class="section" id="announcements">
        <h3><i class="fas fa-bullhorn"></i> Manage Announcements</h3>
        <a href="add_announcement.php" class="btn primary"><i class="fas fa-plus"></i> Post Announcement</a>
        <a href="manage_announcements.php" class="btn secondary"><i class="fas fa-edit"></i> Manage Announcements</a>
    </div>

    <!-- Live Classes -->
    <div class="section" id="liveclasses">
        <h3><i class="fas fa-video"></i> Live Classes</h3>
        <ul>
            <li><a href="create_live_class.php" class="btn success"><i class="fas fa-plus-circle"></i> Create Live Class</a></li>
            <li><a href="manage_live_classes.php" class="btn warning"><i class="fas fa-eye"></i> Manage Live Sessions</a></li>
        </ul>
    </div>
</div>

<script>
    const tabs = document.querySelectorAll('.tab-link');
    const sections = document.querySelectorAll('.section');

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const target = tab.getAttribute('data-tab');

            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            sections.forEach(s => {
                if (s.id === target) s.classList.add('active');
                else s.classList.remove('active');
            });
        });
    });
</script>

</body>
</html>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

$courses = $conn->query("SELECT c.*, u.name AS teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id");

$enrolled = $conn->query("SELECT c.* FROM courses c 
    JOIN enrollments e ON c.id = e.course_id 
    WHERE e.student_id = $student_id");

$quiz_courses = $conn->query("
    SELECT c.id AS course_id, c.name 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    WHERE e.student_id = $student_id
");

$materialQuery = $conn->query("
    SELECT m.title, m.file_path, m.uploaded_at, c.name AS course_name
    FROM course_materials m
    JOIN courses c ON m.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id
    WHERE e.student_id = $student_id
    ORDER BY m.uploaded_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 260px;
            background-color: #2c3e50;
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 30px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            margin-bottom: 10px;
            display: block;
            border-radius: 6px;
            transition: 0.3s;
        }

        .sidebar a i {
            margin-right: 10px;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #34495e;
        }

        .main {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            position: relative;
        }

        .back-home {
            position: absolute;
            top: 20px;
            right: 40px;
        }

        .back-home a {
            padding: 10px 20px;
            background-color: #16a085;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .back-home a:hover {
            background-color: #13856e;
        }

        h3 {
            color: #2c3e50;
            margin-top: 0;
        }

        .course-card, .quiz-card, .material-card {
            background: white;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .course-card strong, .quiz-card strong {
            font-size: 1.1rem;
        }

        .course-card small {
            color: #777;
        }

        form input[type="submit"] {
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        form input[type="submit"]:hover {
            background-color: #219150;
        }

        .material-card a {
            color: #007bff;
            text-decoration: none;
        }

        .material-card a:hover {
            text-decoration: underline;
        }

        .logout {
            margin-top: auto;
            text-align: center;
        }

        .logout a {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border-radius: 6px;
            display: inline-block;
            font-weight: bold;
        }

        .logout a:hover {
            background-color: #c0392b;
        }

        .btn-live {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2980b9;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-live:hover {
            background-color: #1f5f8b;
        }

        .message {
            background-color: #d9edf7;
            color: #31708f;
            padding: 15px;
            text-align: center;
            border: 1px solid #bce8f1;
            border-radius: 5px;
            margin-bottom: 30px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2><i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($name); ?></h2>
    <a href="#" onclick="showSection('enroll')"><i class="fas fa-book-open"></i> Enroll Courses</a>
    <a href="#" onclick="showSection('enrolled')"><i class="fas fa-layer-group"></i> My Courses</a>
    <a href="#" onclick="showSection('quizzes')"><i class="fas fa-clipboard-question"></i> Quizzes</a>
    <a href="#" onclick="showSection('materials')"><i class="fas fa-file-alt"></i> Materials</a>
    <a href="#" onclick="showSection('live')"><i class="fas fa-video"></i> Live Classes</a>
    <a href="view-results.php"><i class="fas fa-chart-bar"></i> Grades</a>
    <a href="view_marks.php"><i class="fas fa-marker"></i> View Marks</a>
    <a href="student-progress.php"><i class="fas fa-chart-line"></i> Progress</a>
    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
    <div class="logout">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- Main Content -->
<div class="main">
    <div class="back-home">
        <a href="index.php"><i class="fas fa-home"></i> Back to Home</a>
    </div>

    <!-- Session message display at top center -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message">
            <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Sections -->
    <div id="enroll" class="section">
        <h3><i class="fas fa-book-open"></i> Enroll in Courses</h3>
        <?php while ($row = $courses->fetch_assoc()) { ?>
            <div class="course-card">
                <strong><?php echo $row['name']; ?></strong><br>
                <small><i class="fas fa-chalkboard-teacher"></i> <?php echo $row['teacher_name']; ?></small><br>
                <form method="POST" action="enroll.php">
                    <input type="hidden" name="course_id" value="<?php echo $row['id']; ?>">
                    <input type="submit" value="Enroll">
                </form>
            </div>
        <?php } ?>
    </div>

    <div id="enrolled" class="section" style="display:none;">
        <h3><i class="fas fa-layer-group"></i> My Enrolled Courses</h3>
        <?php mysqli_data_seek($enrolled, 0); while ($row = $enrolled->fetch_assoc()) { ?>
            <div class="course-card">
                <strong><?php echo $row['name']; ?></strong><br>
                <a href="view-assignments.php?course_id=<?php echo $row['id']; ?>"><i class="fas fa-tasks"></i> View Assignments</a>
            </div>
        <?php } ?>
    </div>

    <div id="quizzes" class="section" style="display:none;">
        <h3><i class="fas fa-clipboard-question"></i> Available Quizzes</h3>
        <?php mysqli_data_seek($quiz_courses, 0); while ($row = $quiz_courses->fetch_assoc()) { ?>
            <div class="quiz-card">
                <strong><i class="fas fa-book"></i> <?php echo $row['name']; ?></strong><br>
                <a href="student_quizzes.php?course_id=<?php echo $row['course_id']; ?>">Take Quiz</a>
            </div>
        <?php } ?>
    </div>

    <div id="materials" class="section" style="display:none;">
        <h3><i class="fas fa-file-alt"></i> Course Materials</h3>
        <?php if ($materialQuery->num_rows > 0): ?>
            <?php mysqli_data_seek($materialQuery, 0); while ($row = $materialQuery->fetch_assoc()): ?>
                <div class="material-card">
                    <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                    <small><?= htmlspecialchars($row['course_name']) ?> | Uploaded: <?= $row['uploaded_at'] ?></small><br>
                    <a href="<?= $row['file_path'] ?>" download>Download</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No materials uploaded yet.</p>
        <?php endif; ?>
    </div>

    <div id="live" class="section" style="display:none;">
        <h3><i class="fas fa-video"></i> Live Classes</h3>
        <p>
            <a href="view_live_classes.php" class="btn-live">
                <i class="fas fa-calendar-alt"></i> View & Join Live Classes
            </a>
        </p>
    </div>
</div>

<script>
    function showSection(id) {
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => section.style.display = 'none');
        document.getElementById(id).style.display = 'block';
    }
</script>

</body>
</html>

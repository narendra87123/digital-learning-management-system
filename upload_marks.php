<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'learning_digital_online', 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST['student_id'];
    $course_name = $_POST['course_name'];
    $assignment1 = $_POST['assignment1'];
    $assignment2 = $_POST['assignment2'];
    $mid_exam = $_POST['mid_exam'];
    $lab_exam = $_POST['lab_exam'];
    $faculty_id = $_SESSION['user_id'];
    $uploaded_at = date('Y-m-d H:i:s');

    // Get course_id from course_name
    $stmt = $conn->prepare("SELECT id FROM courses WHERE name = ?");
    $stmt->bind_param("s", $course_name);
    $stmt->execute();
    $stmt->bind_result($course_id);
    $stmt->fetch();
    $stmt->close();

    if (!$course_id) {
        echo "<p style='color:red;'>Course not found!</p>";
    } else {
        $total = $assignment1 + $assignment2 + $mid_exam + $lab_exam;

        // Insert into marks table
        $stmt = $conn->prepare("INSERT INTO marks (student_id, course_id, faculty_id, class_test, lab_exam, mid_exam, total, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiiiis", $student_id, $course_id, $faculty_id, $assignment1, $lab_exam, $mid_exam, $total, $uploaded_at);
        if ($stmt->execute()) {
            echo "<p style='color:green;'>Marks uploaded successfully!</p>";
        } else {
            echo "<p style='color:red;'>Failed to upload marks: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Fetch course names for dropdown
$course_options = "";
$result = $conn->query("SELECT name FROM courses");
while ($row = $result->fetch_assoc()) {
    $course_options .= "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Marks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e3f2fd;
            padding: 40px;
        }
        h2 {
            text-align: center;
            color: #1e88e5;
        }
        form {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        label {
            display: block;
            margin: 15px 0 5px;
        }
        input[type="number"],
        select,
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }
        input[type="submit"] {
            background-color: #1e88e5;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0d47a1;
        }

        .back-button {
            display: inline-block;
            margin: 15px;
            padding: 8px 16px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<h2>Upload Marks</h2>

<form method="POST" action="">
    <label for="student_id">Student ID:</label>
    <input type="number" name="student_id" required>

    <label for="course_name">Course Name:</label>
    <select name="course_name" required>
        <option value="">Select Course</option>
        <?= $course_options ?>
    </select>

    <label for="assignment1">Assignment 1 Marks:</label>
    <input type="number" name="assignment1" required>

    <label for="assignment2">Assignment 2 Marks:</label>
    <input type="number" name="assignment2" required>

    <label for="mid_exam">Mid Exam Marks:</label>
    <input type="number" name="mid_exam" required>

    <label for="lab_exam">Lab Exam Marks:</label>
    <input type="number" name="lab_exam" required>

    <input type="submit" value="Submit Marks">
</form>
<a href="teacher-dashboard.php" class="back-button">&larr; Back to Dashboard</a>
</body>
</html>

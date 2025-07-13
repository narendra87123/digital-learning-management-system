<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$result = $conn->prepare("SELECT * FROM marks WHERE student_id = ?");
$result->bind_param("i", $student_id);
$result->execute();
$data = $result->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Marks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff8e1;
            padding: 40px;
        }
        h2 {
            text-align: center;
            color: #f57f17;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #ffe082;
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

<h2>Your Marks</h2>

<table>
    <tr>
        <th>Course ID</th>
        <th>Assignment 1</th>
        <th>Assignment 2</th>
        <th>Mid Exam</th>
        <th>Lab Exam</th>
        <th>Total</th>
        <th>Uploaded At</th>
    </tr>

    <?php while ($row = $data->fetch_assoc()): ?>
    <tr>
        <td><?= $row['course_id'] ?></td>
        <td><?= $row['assignment1'] ?></td>
        <td><?= $row['assignment2'] ?></td>
        <td><?= $row['mid_exam'] ?></td>
        <td><?= $row['lab_exam'] ?></td>
        <td><?= $row['total'] ?></td>
        <td><?= $row['uploaded_at'] ?></td>
    </tr>
    <?php endwhile; ?>

</table>
<a href="student-dashboard.php" class="back-button">&larr; Back to Dashboard</a>
</body>
</html>

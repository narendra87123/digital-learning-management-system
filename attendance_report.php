<?php
session_start();
include 'db.php';

// Check login and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Unauthorized access.");
}

$facultyId = $_SESSION['user_id'];

// Corrected SQL query
$sql = "SELECT a.*, u.name AS student_name, lc.title AS class_title
        FROM attendance a
        JOIN users u ON a.student_id = u.id
        JOIN live_classes lc ON a.live_class_id = lc.id
        WHERE lc.faculty_id = $facultyId
        ORDER BY a.attended_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
</head>
<body>
    <h2>Attendance Report</h2>
    <table border="1">
        <tr>
            <th>Student Name</th>
            <th>Class Title</th>
            <th>Attended At</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= htmlspecialchars($row['class_title']) ?></td>
            <td><?= $row['attended_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

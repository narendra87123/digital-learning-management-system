<?php
session_start();
include 'db.php';

// Optional: Prevent access if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

// Optional: Get student ID
$student_id = $_SESSION['user_id'];

// Fetch live class data
$sql = "SELECT lc.*, c.name AS course_name, u.name AS teacher_name
        FROM live_classes lc
        JOIN courses c ON lc.course_id = c.id
        JOIN users u ON lc.faculty_id = u.id
        ORDER BY lc.scheduled_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upcoming Live Classes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 15px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #fff;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #354f6b;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a.join-link {
            background-color: #1e90ff;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
        }

        a.join-link:hover {
            background-color: #0d75d8;
        }
    </style>
</head>
<body>

<a href="student-dashboard.php" class="back-button">&larr; Back to Dashboard</a>

<h2>Upcoming Live Classes</h2>

<?php if ($result && $result->num_rows > 0): ?>
<table>
    <tr>
        <th>Course</th>
        <th>Title</th>
        <th>Teacher</th>
        <th>Platform</th>
        <th>Scheduled At</th>
        <th>Join</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['course_name']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['teacher_name']) ?></td>
            <td><?= htmlspecialchars($row['platform']) ?></td>
            <td><?= date("d M Y, h:i A", strtotime($row['scheduled_at'])) ?></td>
            <td><a href="join_live_class.php?class_id=<?= $row['id'] ?>" class="join-link" target="_blank">Join Class</a></td>
        </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
    <p>No live classes scheduled yet.</p>
<?php endif; ?>

</body>
</html>
<?php
session_start();
include 'db.php';

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];

// Fetch teacher's scheduled live classes
$sql = "SELECT lc.*, c.name AS course_name
        FROM live_classes lc
        JOIN courses c ON lc.course_id = c.id
        WHERE lc.faculty_id = ?
        ORDER BY lc.scheduled_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
?>



<!DOCTYPE html>
<html>
<head>
    <title>Your Scheduled Live Classes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #2e3e4e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ccc;
        }

        th {
            background-color: #394867;
            color: white;
        }

        a {
            background-color: #1abc9c;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        a:hover {
            background-color: #16a085;
        }
    </style>
</head>
<body>

<h2>Your Scheduled Live Classes</h2>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Course</th>
            <th>Title</th>
            <th>Platform</th>
            <th>Scheduled At</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['course_name']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['platform']) ?></td>
                <td><?= date("d M Y, h:i A", strtotime($row['scheduled_at'])) ?></td>
                <td><a href="<?= htmlspecialchars($row['join_link']) ?>" target="_blank">Start Class</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p style="text-align:center;">No live classes scheduled yet.</p>
<?php endif; ?>

</body>
</html>

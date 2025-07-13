<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "learning_digital_online";
$port = 3307;

// Start session and get teacher ID
session_start();
$teacher_id = $_SESSION['teacher_id'] ?? 2; // fallback for testing

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch live classes for the current teacher
$sql = "SELECT * FROM live_classes WHERE faculty_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Live Classes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #999;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #2d89ef;
            color: white;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #2d89ef;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>

    <h2>Live Classes for Teacher ID: <?php echo htmlspecialchars($teacher_id); ?></h2>

    <a class="back-btn" href="teacher-dashboard.php">← Back to Dashboard</a>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Course ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Platform</th>
                <th>Join Link</th>
                <th>Scheduled At</th>
                <th>Created At</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row["id"]; ?></td>
                    <td><?php echo $row["course_id"]; ?></td>
                    <td><?php echo htmlspecialchars($row["title"]); ?></td>
                    <td><?php echo htmlspecialchars($row["description"]); ?></td>
                    <td><?php echo $row["platform"]; ?></td>
                    <td><a href="<?php echo $row["join_link"]; ?>" target="_blank">Join</a></td>
                    <td><?php echo $row["scheduled_at"]; ?></td>
                    <td><?php echo $row["created_at"]; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No live classes scheduled.</p>
    <?php endif; ?>

    <?php
    $stmt->close();
    $conn->close();
    ?>

</body>
</html>

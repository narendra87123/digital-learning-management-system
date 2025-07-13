<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM announcements ORDER BY posted_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Announcements | Digital LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            padding: 40px 20px;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: rgba(255, 255, 255, 0.96);
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f7f7f7;
            color: #333;
        }

        tr:hover {
            background-color: #f0f9ff;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.95rem;
            text-decoration: none;
            color: white;
            transition: background 0.3s ease;
        }

        .btn.edit {
            background-color: #17a2b8;
        }

        .btn.edit:hover {
            background-color: #138496;
        }

        .btn.delete {
            background-color: #dc3545;
        }

        .btn.delete:hover {
            background-color: #b02a37;
        }

        .btn i {
            margin-right: 6px;
        }

        .back-button {
            display: block;
            margin: 30px auto 0;
            text-align: center;
            background-color: #354f6b;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1rem;
            width: fit-content;
        }

        .back-button:hover {
            background-color: #2c3e50;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-bullhorn"></i> Manage Announcements</h2>

    <table>
        <tr>
            <th><i class="fas fa-heading"></i> Title</th>
            <th><i class="fas fa-calendar-alt"></i> Posted At</th>
            <th><i class="fas fa-cogs"></i> Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($row['posted_at'])); ?></td>
                <td>
                    <a class="btn edit" href="edit_announcement.php?id=<?php echo $row['id']; ?>">
                        <i class="fas fa-pen"></i>Edit
                    </a>
                    <a class="btn delete" href="delete_announcement.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this announcement?');">
                        <i class="fas fa-trash"></i>Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <a class="back-button" href="teacher-dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>
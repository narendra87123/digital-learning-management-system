<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id']);
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title && $content) {
        $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $id);
        if ($stmt->execute()) {
            $msg = "Announcement updated!";
        } else {
            $msg = "Update failed.";
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$announcement = $stmt->get_result()->fetch_assoc();

if (!$announcement) {
    echo "Announcement not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Announcement</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        form { background: white; padding: 20px; max-width: 600px; margin: auto; border-radius: 8px; }
        input[type="text"], textarea { width: 100%; padding: 10px; margin: 10px 0; }
        input[type="submit"] { padding: 10px 20px; background: #28a745; color: white; border: none; }
        .msg { text-align: center; color: green; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Edit Announcement</h2>

<?php if ($msg): ?>
    <p class="msg"><?php echo $msg; ?></p>
<?php endif; ?>

<form method="post">
    <label>Title:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($announcement['title']); ?>" required>

    <label>Content:</label>
    <textarea name="content" rows="6" required><?php echo htmlspecialchars($announcement['content']); ?></textarea>

    <input type="submit" value="Update">
</form>

<p style="text-align:center;"><a href="manage_announcements.php">← Back to List</a></p>

</body>
</html>

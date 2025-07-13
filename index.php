<?php
session_start();
include 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$announcements = [];

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE title LIKE ? OR content LIKE ? ORDER BY posted_at DESC");
    $searchTerm = '%' . $search . '%';
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $announcements = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM announcements ORDER BY posted_at DESC LIMIT 5");
    if ($result) {
        $announcements = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Digital LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #333;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            padding: 10px 30px;
            background: rgba(44, 62, 80, 0.95);
            color: white;
        }

        .top-bar .auth-links a {
            color: white;
            margin-left: 15px;
            text-decoration: none;
            font-weight: bold;
        }

        .top-bar .auth-links a:hover {
            text-decoration: underline;
        }

        .header {
            background: rgba(44, 62, 80, 0.95);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }

        .main {
            text-align: center;
            margin-top: 30px;
        }

        .main h2 {
            font-size: 1.6rem;
            color: white;
            text-shadow: 1px 1px 2px #000;
        }

        .buttons a {
            display: inline-block;
            margin: 10px;
            padding: 12px 30px;
            background: #ffffff;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .buttons a:hover {
            background: #ecf0f1;
            transform: scale(1.05);
        }

        .section {
            max-width: 900px;
            margin: 40px auto;
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .section h3 {
            text-align: center;
            font-size: 1.6rem;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .announcement {
            border-bottom: 1px solid #e0e0e0;
            padding: 20px 0;
        }

        .announcement:last-child {
            border-bottom: none;
        }

        .announcement h4 {
            margin: 0 0 10px;
            font-size: 1.2rem;
            color: #2c3e50;
        }

        .announcement p {
            margin: 0 0 8px;
            color: #555;
            line-height: 1.6;
        }

        .announcement small {
            color: #999;
            font-style: italic;
        }

        form.search-form {
            text-align: center;
            margin-bottom: 30px;
        }

        input[type="text"] {
            padding: 10px 15px;
            width: 60%;
            max-width: 400px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            margin-left: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background: #1a252f;
        }

        .footer {
            text-align: center;
            padding: 30px;
            background: rgba(44, 62, 80, 0.95);
            color: white;
        }

        .footer img {
            max-width: 150px;
            margin-top: 15px;
            border-radius: 12px;
        }

        .footer p {
            margin: 10px auto;
            max-width: 600px;
            font-style: italic;
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <div><i class="fas fa-graduation-cap"></i> Digital LMS</div>
    <div class="auth-links">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
        <?php else: ?>
            <a href="<?php echo ($_SESSION['role'] === 'teacher') ? 'teacher-dashboard.php' : 'student-dashboard.php'; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php endif; ?>
    </div>
</div>

<!-- Header -->
<div class="header">
    <h1><i class="fas fa-laptop-code"></i> Digital Learning Management System</h1>
</div>

<!-- Main -->
<div class="main">
   <h2>
    Welcome<?php
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
        echo ', ' . htmlspecialchars($_SESSION['user_name']);
    } else {
        echo ', Guest';
    }
    ?>!
</h2>

</div>

<!-- Announcements -->
<div class="section">
    <h3><i class="fas fa-bullhorn"></i> Latest Announcements</h3>
    <form class="search-form" method="get">
        <input type="text" name="search" placeholder="Search announcements..." value="<?php echo htmlspecialchars($search); ?>">
        <input type="submit" value="Search">
    </form>

    <?php if ($announcements && $announcements->num_rows > 0): ?>
        <?php while ($row = $announcements->fetch_assoc()): ?>
            <div class="announcement">
                <h4><i class="fas fa-bookmark"></i> <?php echo htmlspecialchars($row['title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                <small><i class="fas fa-clock"></i> Posted on: <?php echo date('F j, Y, g:i A', strtotime($row['posted_at'])); ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No announcements found.</p>
    <?php endif; ?>
</div>

<!-- About Us -->
<div class="section">
    <h3><i class="fas fa-info-circle"></i> About Us</h3>
    <p>We are a passionate team dedicated to improving education through technology. Our Digital LMS provides tools for teachers and students to connect, collaborate, and grow through structured digital learning environments.</p>
</div>

<!-- Contact Us -->
<div class="section">
    <h3><i class="fas fa-envelope"></i> Contact Us</h3>
    <p>Email: support@digitallms.com</p>
    <p>Phone: 8712399740</p>
    <p>Location: 123 Learning Way, EduCity, Knowledge State</p>
</div>

<!-- Footer -->
<div class="footer">
    <p><i class="fas fa-quote-left"></i> “Education is the passport to the future, for tomorrow belongs to those who prepare for it today.”</p>
    <img src="https://images.unsplash.com/photo-1529070538774-1843cb3265df?auto=format&fit=crop&w=400&q=80" alt="Motivation">
    <p>&copy; <?php echo date('Y'); ?> Digital LMS. All rights reserved.</p>
</div>

</body>
</html>

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id']);
$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];

    $stmt = $conn->prepare("INSERT INTO quizzes (course_id, title, description, deadline) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $course_id, $title, $description, $deadline);
    $stmt->execute();
    $quiz_id = $stmt->insert_id;

    for ($i = 0; $i < count($_POST['questions']); $i++) {
        $q = $_POST['questions'][$i];
        $stmt2 = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("issssss", $quiz_id, $q['question'], $q['a'], $q['b'], $q['c'], $q['d'], $q['correct']);
        $stmt2->execute();
    }

    $msg = "✅ Quiz uploaded successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Quiz | Digital LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(-45deg, #7f7fd5, #86a8e7, #91eae4, #d4fc79);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 850px;
            margin: 60px auto;
            background: rgba(255, 255, 255, 0.96);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #34495e;
        }

        form input[type="text"],
        form input[type="datetime-local"],
        form textarea,
        form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        .question-block {
            margin-top: 25px;
            padding: 20px;
            background-color: #f8f9fa;
            border-left: 4px solid #7f7fd5;
            border-radius: 10px;
        }

        button, input[type="submit"] {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            margin-top: 25px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover, input[type="submit"]:hover {
            background-color: #1a252f;
        }

        .back-button {
            display: inline-block;
            margin-top: 25px;
            background-color: #354f6b;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.95rem;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background-color: #2c3e50;
        }

        .message {
            margin-top: 20px;
            font-style: italic;
            text-align: center;
            color: #2c3e50;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-clipboard-question"></i> Upload Quiz</h2>

    <?php if ($msg): ?>
        <div class="message"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">
        <label><i class="fas fa-heading"></i> Quiz Title:</label>
        <input type="text" name="title" required>

        <label><i class="fas fa-align-left"></i> Description:</label>
        <textarea name="description"></textarea>

        <label><i class="fas fa-calendar-alt"></i> Deadline:</label>
        <input type="datetime-local" name="deadline" required>

        <div id="questions"></div>

        <button type="button" onclick="addQuestion()">
            <i class="fas fa-plus-circle"></i> Add Question
        </button>

        <input type="submit" value="Upload Quiz">
    </form>

    <a href="teacher-dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<script>
let qIndex = 0;
function addQuestion() {
    const container = document.getElementById('questions');
    const html = `
        <div class="question-block">
            <label><i class="fas fa-question-circle"></i> Question ${qIndex + 1}</label>
            <input type="text" name="questions[${qIndex}][question]" required>

            <label>Option A:</label>
            <input type="text" name="questions[${qIndex}][a]" required>

            <label>Option B:</label>
            <input type="text" name="questions[${qIndex}][b]" required>

            <label>Option C:</label>
            <input type="text" name="questions[${qIndex}][c]" required>

            <label>Option D:</label>
            <input type="text" name="questions[${qIndex}][d]" required>

            <label>Correct Option:</label>
            <select name="questions[${qIndex}][correct]" required>
                <option value="">-- Select --</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    qIndex++;
}
</script>

</body>
</html>
<?php
include 'db.php';
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            if ($user['role'] == 'student') {
                header("Location: student-dashboard.php");
            } elseif ($user['role'] == 'teacher') {
                header("Location: teacher-dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Digital LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            max-width: 450px;
            margin: 100px auto;
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
            margin: 15px 0 5px;
            font-weight: bold;
            color: #34495e;
        }

        form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 40px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }

        form input[type="submit"] {
            background-color: #2c3e50;
            color: white;
            border: none;
            margin-top: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        form input[type="submit"]:hover {
            background-color: #1a252f;
        }

        p {
            margin-top: 20px;
            font-style: italic;
            text-align: center;
            color: #2c3e50;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
        }

        .back-home {
            display: inline-block;
            text-align: center;
            margin: 25px auto 0;
            padding: 10px 25px;
            background-color: #354f6b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: background 0.3s ease;
            display: block;
            width: fit-content;
        }

        .back-home:hover {
            background-color: #2c3e50;
        }

        a {
            color: #1e88e5;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-sign-in-alt"></i> Login</h2>

    <form method="POST" action="">
        <label for="email"><i class="fas fa-envelope"></i> Email:</label>
        <input type="email" name="email" required>

        <label for="password"><i class="fas fa-lock"></i> Password:</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" required>
            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
        </div>

        <input type="submit" value="Login">
    </form>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
    <a class="back-home" href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
</div>

<!-- JavaScript -->
<script>
    const passwordField = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', () => {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        togglePassword.classList.toggle('fa-eye');
        togglePassword.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>
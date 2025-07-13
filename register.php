<?php
include 'db.php'; // Your DB connection config

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $password   = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role       = $_POST['role'];
    $gender     = $_POST['gender'];
    $dob        = $_POST['dob'];
    $address    = trim($_POST['address']);
    $department = trim($_POST['department']);

    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    $status     = 'active';
    $profile_picture = '';

    // Handle picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = uniqid() . "_" . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = $target_file;
        } else {
            die("Error uploading profile picture.");
        }
    }

    $stmt = $conn->prepare("INSERT INTO users 
        (name, email, phone, password, role, gender, dob, address, department, profile_picture, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssssss", 
        $name, $email, $phone, $password, $role, $gender, $dob, $address, $department, $profile_picture, $status, $created_at, $updated_at);

    if ($stmt->execute()) {
        $message = "🎉 Registration successful!";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Digital LMS</title>
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
    max-width: 700px;
    margin: 70px auto;
    background: #fff;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

h2 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 30px;
}

.form-layout {
    display: flex;
    gap: 30px;
    align-items: flex-start;
    flex-wrap: wrap;
}

.profile-section {
    flex: 0 0 140px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.profile-section img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ccc;
    margin-bottom: 10px;
}

.form-fields {
    flex: 1;
    min-width: 250px;
}

form label {
    display: block;
    font-weight: 600;
    color: #333;
    margin: 12px 0 5px;
}

form input, form select, form textarea {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    box-sizing: border-box;
}

form input[type="submit"] {
    background: #2c3e50;
    color: white;
    border: none;
    font-weight: bold;
    cursor: pointer;
    margin-top: 20px;
}

form input[type="submit"]:hover {
    background: #1a252f;
}

.password-wrapper {
    position: relative;
}

.password-wrapper input {
    padding-right: 40px; /* space for eye icon */
}

.toggle-password {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    cursor: pointer;
    color: #777;
    font-size: 1.1rem;
}

.message {
    color: green;
    font-weight: bold;
    text-align: center;
    margin-top: 20px;
}

.back-home {
    display: block;
    text-align: center;
    margin-top: 10px;
    color: #2c3e50;
    text-decoration: none;
    font-weight: 500;
}

.back-home:hover {
    text-decoration: underline;
}
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-user-plus"></i> Register</h2>

   <form method="post" enctype="multipart/form-data" onsubmit="return validatePassword();">
    <div class="form-layout">
        <div class="profile-section">
            <img id="preview" src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Preview">
            <input type="file" name="profile_picture" accept="image/*" required onchange="previewImage(event)">
        </div>

        <div class="form-fields">
            <label>Name:</label>
            <input type="text" name="name" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Phone:</label>
            <input type="text" name="phone" required>

            <label>Password:</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" required>
                <i class="fa fa-eye toggle-password" id="togglePassword"></i>
            </div>

            <label>Role:</label>
            <select name="role" required>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>

            <label>Department:</label>
            <input type="text" name="department" required>

            <label>Gender:</label>
            <select name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label>Date of Birth:</label>
            <input type="date" name="dob" required>

            <label>Address:</label>
            <textarea name="address" required></textarea>

            <input type="submit" value="Register">
        </div>
    </div>
</form>

    <p class="message"><?= $message ?></p>
    <a class="back-home" href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
</div>

<script>
    // Toggle password
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    toggle.addEventListener('click', () => {
        const type = password.type === 'password' ? 'text' : 'password';
        password.type = type;
        toggle.classList.toggle('fa-eye-slash');
    });

    // Password validation
    function validatePassword() {
        const val = password.value;
        const isValid =
            val.length >= 8 &&
            /[A-Z]/.test(val) &&
            /[a-z]/.test(val) &&
            /[0-9]/.test(val) &&
            /[!@#$%^&*]/.test(val);

        if (!isValid) {
            alert("⚠ Password must include 8+ characters, uppercase, lowercase, number & special character.");
            return false;
        }
        return true;
    }

    // Image preview
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            document.getElementById('preview').src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</body>
</html>

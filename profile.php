<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: index.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$message = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $address = trim($_POST['address']);
    $department = trim($_POST['department']);
    $updated_at = date('Y-m-d H:i:s');

    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = uniqid() . "_" . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Delete old image
            if (!empty($user['profile_picture']) && file_exists("uploads/" . $user['profile_picture'])) {
                unlink("uploads/" . $user['profile_picture']);
            }
            $user['profile_picture'] = $file_name;
        }
    }

    // Update in DB
    $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, gender=?, dob=?, address=?, department=?, profile_picture=?, updated_at=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $name, $phone, $gender, $dob, $address, $department, $user['profile_picture'], $updated_at, $user_id);

    if ($stmt->execute()) {
        $message = "✅ Profile updated successfully.";
        $user = array_merge($user, compact('name', 'phone', 'gender', 'dob', 'address', 'department'));
    } else {
        $message = "❌ Error updating profile: " . $stmt->error;
    }
    $stmt->close();
}

// Handle profile picture removal
if (isset($_GET['remove_picture']) && $user['profile_picture']) {
    $file_to_delete = "uploads/" . $user['profile_picture'];
    if (file_exists($file_to_delete)) unlink($file_to_delete);

    $stmt = $conn->prepare("UPDATE users SET profile_picture='' WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $user['profile_picture'] = "";
    $message = "🗑️ Profile picture removed.";
}

$default_image = "https://cdn-icons-png.flaticon.com/512/149/149071.png";
$image_path = (!empty($user['profile_picture']) && file_exists("uploads/" . $user['profile_picture']))
    ? "uploads/" . $user['profile_picture']
    : $default_image;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Digital LMS</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7f9;
            margin: 0;
            padding: 0;
        }
        .profile-container {
            max-width: 650px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        .top-bar {
            position: absolute;
            bottom: -50px;
            right: 0;
        }
        .top-bar a {
            background: #007bff;
            color: #fff;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
        }
        .profile-container img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ccc;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 4px;
            color: #333;
        }
        input[type="text"], input[type="date"], input[type="file"], select, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        textarea {
            height: 70px;
        }
        .btn-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .save-btn {
            background-color: #28a745;
            color: #fff;
        }
        .remove-btn {
            background-color: #dc3545;
            color: #fff;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            margin-top: 10px;
            display: inline-block;
        }
        .message {
            color: green;
            font-weight: bold;
            margin: 10px 0;
        }

        .top-bar {
    position: absolute;
    top: 20px;
    right: 20px;
}

    </style>
</head>
<body>
<div class="profile-container">
    <div class="top-bar">
        <a href="<?= ($user['role'] === 'student') ? 'student-dashboard.php' : 'teacher-dashboard.php' ?>">← Back to Dashboard</a>
    </div>

    <h2>Edit Your Profile</h2>
    <img src="<?= htmlspecialchars($image_path) ?>" alt="Profile Picture"><br><br>

    <?php if (!empty($user['profile_picture'])): ?>
        <a href="?remove_picture=1" class="remove-btn">🗑️ Remove Picture</a>
    <?php endif; ?>

    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Change Profile Picture:</label>
            <input type="file" name="profile_picture" accept="image/*">
        </div>

        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
        </div>
        <div class="form-group">
            <label>Gender:</label>
            <select name="gender" required>
                <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
            </select>
        </div>
        <div class="form-group">
            <label>Date of Birth:</label>
            <input type="date" name="dob" value="<?= $user['dob'] ?>" required>
        </div>
        <div class="form-group">
            <label>Department:</label>
            <input type="text" name="department" value="<?= htmlspecialchars($user['department']) ?>" required>
        </div>
        <div class="form-group">
            <label>Address:</label>
            <textarea name="address" required><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" name="update_profile" class="save-btn">💾 Save Changes</button>
        </div>
    </form>
</div>
</body>
</html>

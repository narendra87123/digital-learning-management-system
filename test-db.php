<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'learning_digital_online';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Connected to MySQL successfully!";
}
?>

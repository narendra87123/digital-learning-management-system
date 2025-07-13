<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'learning_digital_online';
$port = 3307; // ← important!

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>

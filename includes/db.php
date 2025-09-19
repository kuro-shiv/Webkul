<?php
$host = "localhost"; // ✅ correct for XAMPP
$user = "root";      // ✅ default MySQL username
$pass = "";          // ✅ default MySQL password (empty in XAMPP)
$dbname = "social_network_db"; // ✅ must match DB in phpMyAdmin

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notificationId = $_POST['id'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
    $stmt->bindParam(':id', $notificationId);
    $stmt->execute();
}
?>

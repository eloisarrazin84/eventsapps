<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['notification_id'])) {
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$notification_id = $_POST['notification_id'];

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Marquer la notification comme lue
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :notification_id AND user_id = :user_id");
$stmt->bindParam(':notification_id', $notification_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

echo 'Notification marquée comme lue.';
?>

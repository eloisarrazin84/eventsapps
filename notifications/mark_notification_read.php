<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la page de connexion si non connecté
    exit();
}

$user_id = $_SESSION['user_id'];

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :notification_id AND user_id = :user_id");
    $stmt->bindParam(':notification_id', $notification_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to mark notification as read.']);
    }
}
?>

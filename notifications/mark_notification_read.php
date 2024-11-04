<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    // Connexion à la base de données
    $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $notification_id = $_POST['notification_id'];
    $user_id = $_SESSION['user_id'];

    // Marquer la notification comme lue
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :notification_id AND user_id = :user_id");
    $stmt->bindParam(':notification_id', $notification_id);
    $stmt->bindParam(':user_id', $user_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to mark notification as read']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No notification ID provided']);
}
?>

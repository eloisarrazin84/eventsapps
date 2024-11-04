<?php
session_start();
include 'db_connection.php'; // Inclure votre fichier de connexion à la base de données

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['notification_id'])) {
        // Marquer une seule notification comme lue
        $notification_id = $_POST['notification_id'];
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :notification_id AND user_id = :user_id");
        $stmt->bindParam(':notification_id', $notification_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    } elseif (isset($_POST['mark_all_as_read'])) {
        // Marquer toutes les notifications comme lues
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }

    echo json_encode(['status' => 'success']);
}
?>

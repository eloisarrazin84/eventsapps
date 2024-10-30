<?php
session_start();
if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $notificationId = intval($_GET['id']);
    $userId = $_SESSION['user_id'];

    try {
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Mettre à jour la notification pour la marquer comme lue
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :notification_id AND user_id = :user_id");
        $stmt->bindParam(':notification_id', $notificationId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
}
?>

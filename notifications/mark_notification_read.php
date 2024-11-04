<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :notification_id AND user_id = :user_id");
    $stmt->bindParam(':notification_id', $notification_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not mark as read']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No notification ID provided']);
}
?>

<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
}

header("Location: previous_page.php"); // Remplacez par la page précédente
exit();
?>

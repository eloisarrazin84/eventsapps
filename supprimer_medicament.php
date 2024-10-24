<?php
session_start();
if (isset($_GET['id'])) {
    $medicament_id = $_GET['id'];
    try {
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("DELETE FROM medicaments WHERE id = :id");
        $stmt->bindParam(':id', $medicament_id);
        $stmt->execute();

        header('Location: gestion_medicaments.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    header('Location: gestion_medicaments.php');
    exit();
}
?>

<?php
session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO medicaments (nom, description, quantite, date_expiration, categorie, user_id) VALUES (:nom, :description, :quantite, :date_expiration, :categorie, :user_id)");
        $stmt->bindParam(':nom', $_POST['nom']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':quantite', $_POST['quantite']);
        $stmt->bindParam(':date_expiration', $_POST['date_expiration']);
        $stmt->bindParam(':categorie', $_POST['categorie']);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        header('Location: gestion_medicaments.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les données du formulaire
        $nom = htmlspecialchars($_POST['medicament_nom']);
        $description = htmlspecialchars($_POST['description']);
        $quantite = intval($_POST['quantite']);
        $date_expiration = $_POST['date_expiration'];
        $categorie = htmlspecialchars($_POST['categorie']);

        // Insertion dans la base de données
        $stmt = $conn->prepare("INSERT INTO medicaments (nom, description, quantite, date_expiration, categorie) VALUES (:nom, :description, :quantite, :date_expiration, :categorie)");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantite', $quantite);
        $stmt->bindParam(':date_expiration', $date_expiration);
        $stmt->bindParam(':categorie', $categorie);

        $stmt->execute();
        echo "Médicament ajouté avec succès.";
        header('Location: gestion_medicaments.php');
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

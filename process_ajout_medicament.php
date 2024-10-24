<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['medicament_nom'];
    $description = $_POST['description'];
    $quantite = $_POST['quantite'];
    $date_expiration = $_POST['date_expiration'];
    $categorie = $_POST['categorie'];

    try {
        // Connexion à la base de données
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insertion des données dans la base de données
        $stmt = $conn->prepare("INSERT INTO medicaments (nom, description, quantite, date_expiration, categorie) VALUES (:nom, :description, :quantite, :date_expiration, :categorie)");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantite', $quantite);
        $stmt->bindParam(':date_expiration', $date_expiration);
        $stmt->bindParam(':categorie', $categorie);

        $stmt->execute();

        // Rediriger vers la page de gestion des médicaments
        header('Location: gestion_medicaments.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

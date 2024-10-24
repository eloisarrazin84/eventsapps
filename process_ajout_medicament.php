<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['medicament_nom'];
    $description = $_POST['description'];
    $numero_lot = $_POST['numero_lot'];
    $quantite = $_POST['quantite'];
    $date_expiration = $_POST['date_expiration'];
    $type_produit = $_POST['type_produit'];

    try {
        // Connexion à la base de données
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insertion des données dans la base de données
        $stmt = $conn->prepare("INSERT INTO medicaments (nom, description, numero_lot, quantite, date_expiration, type_produit) VALUES (:nom, :description, :numero_lot, :quantite, :date_expiration, :type_produit)");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':numero_lot', $numero_lot);
        $stmt->bindParam(':quantite', $quantite);
        $stmt->bindParam(':date_expiration', $date_expiration);
        $stmt->bindParam(':type_produit', $type_produit);

        $stmt->execute();

        // Rediriger vers la page de gestion des médicaments
        header('Location: gestion_medicaments.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

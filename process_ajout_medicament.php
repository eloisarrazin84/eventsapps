<?php
// Démarrer la session et se connecter à la base de données
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier si les données sont envoyées via le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les valeurs du formulaire
    $nom = $_POST['medicament_nom'];
    $numero_lot = $_POST['numero_lot'];
    $description = $_POST['description'];
    $quantite = $_POST['quantite'];
    $date_expiration = $_POST['date_expiration'];
    $type_produit = $_POST['type_produit'];
    $stock_location_id = $_POST['stock_location_id']; // Récupérer l'ID du lieu de stockage

    try {
        // Préparer et exécuter la requête d'insertion
        $stmt = $conn->prepare("INSERT INTO medicaments (nom, numero_lot, description, quantite, date_expiration, type_produit, stock_location_id) 
                                VALUES (:nom, :numero_lot, :description, :quantite, :date_expiration, :type_produit, :stock_location_id)");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':numero_lot', $numero_lot);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantite', $quantite);
        $stmt->bindParam(':date_expiration', $date_expiration);
        $stmt->bindParam(':type_produit', $type_produit);
        $stmt->bindParam(':stock_location_id', $stock_location_id); // Lier le lieu de stockage

        $stmt->execute();

        // Redirection ou message de confirmation
        header('Location: gestion_medicaments.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "Aucune donnée reçue.";
}
?>

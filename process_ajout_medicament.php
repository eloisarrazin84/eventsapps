<?php
session_start();

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['medicament_nom'];
    $numero_lot = $_POST['numero_lot'];
    $description = $_POST['description'];
    $quantite = $_POST['quantite'];
    $date_expiration = $_POST['date_expiration'];
    $type_produit = $_POST['type_produit'];
    $stock_location_id = $_POST['stock_location_id'];
    
    // Initialiser le chemin de la photo
    $photoPath = null;

    // Gérer l'upload de la photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $uploadDir = 'uploads/';
        
        // Créer le dossier d'upload s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $photoName = uniqid() . '_' . basename($_FILES['photo']['name']);
        $uploadFile = $uploadDir . $photoName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
            $photoPath = $uploadFile;
        }
    }

    // Préparer la requête d'insertion
    $stmt = $conn->prepare("INSERT INTO medicaments (nom, numero_lot, description, quantite, date_expiration, type_produit, stock_location_id, photo_path) VALUES (:nom, :numero_lot, :description, :quantite, :date_expiration, :type_produit, :stock_location_id, :photo_path)");
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':numero_lot', $numero_lot);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':quantite', $quantite);
    $stmt->bindParam(':date_expiration', $date_expiration);
    $stmt->bindParam(':type_produit', $type_produit);
    $stmt->bindParam(':stock_location_id', $stock_location_id);
    $stmt->bindParam(':photo_path', $photoPath);
    
    $stmt->execute();

    // Redirection après insertion
    header("Location: gestion_medicaments.php");
    exit();
}
?>

<?php
session_start();
if (isset($_GET['id'])) {
    $medicament_id = $_GET['id'];
    try {
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les informations actuelles du médicament
        $stmt = $conn->prepare("SELECT * FROM medicaments WHERE id = :id");
        $stmt->bindParam(':id', $medicament_id);
        $stmt->execute();
        $medicament = $stmt->fetch(PDO::FETCH_ASSOC);

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $stmt = $conn->prepare("UPDATE medicaments SET nom = :nom, description = :description, quantite = :quantite, date_expiration = :date_expiration, categorie = :categorie WHERE id = :id");
            $stmt->bindParam(':nom', $_POST['nom']);
            $stmt->bindParam(':description', $_POST['description']);
            $stmt->bindParam(':quantite', $_POST['quantite']);
            $stmt->bindParam(':date_expiration', $_POST['date_expiration']);
            $stmt->bindParam(':categorie', $_POST['categorie']);
            $stmt->bindParam(':id', $medicament_id);
            $stmt->execute();

            header('Location: gestion_medicaments.php');
            exit();
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    header('Location: gestion_medicaments.php');
    exit();
}
?>

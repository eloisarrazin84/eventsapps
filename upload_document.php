<?php
session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file = $_FILES['documents'];
    $uploadDir = 'uploads/';
    $documentName = htmlspecialchars($_POST['document_name']);  // Nom personnalisé

    // Si l'utilisateur n'a pas entré de nom, utiliser le nom du fichier
    if (empty($documentName)) {
        $documentName = basename($file['name'][0]);  // Si le nom n'est pas saisi, utiliser le nom du fichier
    }

    // Assurez-vous que le dossier existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . basename($file['name'][0]);

    if (move_uploaded_file($file['tmp_name'][0], $filePath)) {
        // Sauvegarder le document dans la base de données
        try {
            $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare("INSERT INTO documents (user_id, document_name, file_path) VALUES (:user_id, :document_name, :file_path)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':document_name', $documentName);
            $stmt->bindParam(':file_path', $filePath);
            $stmt->execute();

            echo "Fichier téléchargé avec succès.";
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    } else {
        echo "Erreur lors du téléchargement du fichier.";
    }

    // Redirection après le traitement du formulaire
    header('Location: profile.php');
    exit();
}

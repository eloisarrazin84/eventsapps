<?php
session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $uploadDir = 'uploads/profile_pictures/';  // Dossier de téléchargement
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Vérification de la taille et du type MIME
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if ($file['size'] > 500000) {  // Limite de taille de 500 KB
        echo "Erreur : le fichier est trop volumineux.";
        exit();
    } elseif (!in_array($file['type'], $allowedTypes)) {
        echo "Erreur : le type de fichier n'est pas autorisé.";
        exit();
    }

    $filePath = $uploadDir . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Mettre à jour la base de données avec le chemin de la photo
        try {
            $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
            $stmt = $conn->prepare("UPDATE users SET profile_picture = :file_path WHERE id = :user_id");
            $stmt->bindParam(':file_path', $filePath);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            header("Location: profile.php");
            exit();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    } else {
        echo "Erreur lors du téléchargement de la photo.";
    }
}

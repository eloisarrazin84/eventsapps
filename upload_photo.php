<?php
session_start();
$user_id = $_SESSION['user_id'];

// Vérifiez si l'utilisateur est connecté
if (!isset($user_id)) {
    header("Location: login.php");
    exit();
}

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
        echo "<script>alert('Erreur : le fichier est trop volumineux.');</script>";
        exit();
    } elseif (!in_array($file['type'], $allowedTypes)) {
        echo "<script>alert('Erreur : le type de fichier n\'est pas autorisé.');</script>";
        exit();
    }

    // Générer un nom de fichier unique pour éviter les collisions
    $fileName = uniqid() . '-' . basename($file['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Mettre à jour la base de données avec le chemin de la photo
        try {
            $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("UPDATE users SET profile_picture = :file_path WHERE id = :user_id");
            $stmt->bindParam(':file_path', $filePath);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            // Rediriger vers le profil
            header("Location: profile.php");
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Erreur : " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Erreur lors du téléchargement de la photo.');</script>";
    }
} else {
    echo "<script>alert('Aucun fichier sélectionné.');</script>";
}
?>

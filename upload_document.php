<?php
session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['documents'])) {
    $uploadDir = 'uploads/documents/';  // Dossier de téléchargement

    // Créer le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['documents']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['documents']['name'][$key]);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $filePath)) {
            try {
                // Connexion à la base de données
                $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Sauvegarder le document dans la base de données
                $stmt = $conn->prepare("INSERT INTO documents (user_id, document_name, file_path) VALUES (:user_id, :document_name, :file_path)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':document_name', $fileName);
                $stmt->bindParam(':file_path', $filePath);
                $stmt->execute();
            } catch (PDOException $e) {
                echo "Erreur : " . $e->getMessage();
            }
        } else {
            echo "Erreur lors du téléchargement du fichier.";
        }
    }

    // Redirection après le téléchargement
    header('Location: profile.php');
    exit();
}
?>

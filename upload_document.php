<?php
session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $files = $_FILES['documents'];
    $documentNames = $_POST['document_names'];
    $uploadDir = 'uploads/';

    // Assurez-vous que le dossier existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    for ($i = 0; $i < count($files['name']); $i++) {
        if (isset($files['name'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
            // Utilisez un identifiant unique pour éviter les conflits de noms de fichiers
            $filePath = $uploadDir . uniqid() . '_' . basename($files['name'][$i]);
            // Utiliser le nom donné par l'utilisateur ou le nom de fichier par défaut
            $documentName = !empty($documentNames[$i]) ? htmlspecialchars($documentNames[$i]) : basename($files['name'][$i]);

            if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                // Sauvegarder le document dans la base de données
                try {
                    $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $conn->prepare("INSERT INTO documents (user_id, document_name, file_path) VALUES (:user_id, :document_name, :file_path)");
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':document_name', $documentName);
                    $stmt->bindParam(':file_path', $filePath);
                    $stmt->execute();
                } catch (PDOException $e) {
                    echo "Erreur : " . $e->getMessage();
                }
            } else {
                echo "Erreur lors du téléchargement du fichier.";
            }
        }
    }

    // Rediriger après le téléchargement
    header('Location: profile.php');
    exit();
}
?>

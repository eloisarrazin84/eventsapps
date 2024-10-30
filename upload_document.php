<?php
session_start();
$user_id = $_SESSION['user_id'];

// Vérifiez si l'utilisateur est connecté
if (!isset($user_id)) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['documents'])) {
    $files = $_FILES['documents'];
    $documentNames = $_POST['document_names'];
    $uploadDir = 'uploads/';

    // Assurez-vous que le dossier existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Définir les types de fichiers autorisés
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/gif'];

    for ($i = 0; $i < count($files['name']); $i++) {
        if (isset($files['name'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
            // Vérifiez le type MIME
            if (!in_array($files['type'][$i], $allowedTypes)) {
                echo "<script>alert('Erreur : Le type de fichier " . htmlspecialchars($files['name'][$i]) . " n\'est pas autorisé.');</script>";
                continue;  // Passez au fichier suivant
            }

            // Vérifiez la taille du fichier (limite de 5 Mo par exemple)
            if ($files['size'][$i] > 5000000) {
                echo "<script>alert('Erreur : Le fichier " . htmlspecialchars($files['name'][$i]) . " est trop volumineux.');</script>";
                continue;  // Passez au fichier suivant
            }

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
                    echo "<script>alert('Erreur lors de l\'insertion dans la base de données : " . $e->getMessage() . "');</script>";
                }
            } else {
                echo "<script>alert('Erreur lors du téléchargement du fichier " . htmlspecialchars($files['name'][$i]) . ".');</script>";
            }
        } else {
            echo "<script>alert('Erreur lors du traitement du fichier " . htmlspecialchars($files['name'][$i]) . ".');</script>";
        }
    }

    // Rediriger après le téléchargement
    header('Location: profile.php');
    exit();
}
?>

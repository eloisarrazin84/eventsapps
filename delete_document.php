<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $document_id = $_GET['id'];

    // Connexion à la base de données
    $servername = "localhost";
    $username_db = "root";  
    $password_db = "Lipton2019!";
    $dbname = "outdoorsec";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer le chemin du fichier à supprimer
        $stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = :id");
        $stmt->bindParam(':id', $document_id);
        $stmt->execute();
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($document) {
            // Supprimer le fichier du serveur
            $file_path = $document['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path); // Supprimer le fichier du serveur
            }

            // Supprimer l'entrée de la base de données
            $stmt = $conn->prepare("DELETE FROM documents WHERE id = :id");
            $stmt->bindParam(':id', $document_id);
            $stmt->execute();

            // Rediriger avec succès
            header("Location: profile.php");
            exit();
        } else {
            echo "Document non trouvé.";
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "ID de document non spécifié.";
}
?>

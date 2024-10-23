<?php
session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file = $_FILES['file'];
    $uploadDir = 'uploads/';

    // Ensure directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . basename($file['name']);
    $documentName = htmlspecialchars(basename($file['name']));

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Save document to the database
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

    // Redirect after handling form submission
    header('Location: profile.php');
    exit();
}

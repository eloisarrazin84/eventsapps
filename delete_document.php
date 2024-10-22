<?php
session_start();

// Verify that the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to database
$servername = "localhost";
$username_db = "root";  
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $document_id = $_POST['document_id'];

        // Fetch document info
        $stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = :document_id");
        $stmt->bindParam(':document_id', $document_id);
        $stmt->execute();
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($document) {
            // Delete document from filesystem
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }

            // Delete document from database
            $stmt = $conn->prepare("DELETE FROM documents WHERE id = :document_id");
            $stmt->bindParam(':document_id', $document_id);
            $stmt->execute();

            // Redirect back to profile page
            header("Location: profile.php");
            exit();
        } else {
            echo "Document not found.";
        }
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $eventId = $_POST['event_id'];
    $userId = $_SESSION['user_id'];

    // Connexion à la base de données
    $servername = "localhost";
    $username = "root";  
    $password = "Lipton2019!";
    $dbname = "outdoorsec";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier si l'utilisateur est déjà inscrit à cet événement
        $stmt = $conn->prepare("SELECT * FROM event_user_assignments WHERE event_id = :event_id AND user_id = :user_id");
        $stmt->bindParam(':event_id', $eventId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            // Inscrire l'utilisateur à l'événement
            $stmt = $conn->prepare("INSERT INTO event_user_assignments (event_id, user_id) VALUES (:event_id, :user_id)");
            $stmt->bindParam(':event_id', $eventId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        }

        header("Location: dashboard.php");
        exit();

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

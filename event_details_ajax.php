<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";  
$password = "Lipton2019!";
$dbname = "outdoorsec";

if (isset($_GET['id'])) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les détails de l'événement
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $_GET['id']);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            echo "<h5>" . htmlspecialchars($event['event_name']) . "</h5>";
            echo "<p><strong>Date :</strong> " . htmlspecialchars($event['event_date']) . "</p>";
            echo "<p><strong>Lieu :</strong> " . htmlspecialchars($event['event_location']) . "</p>";
            echo "<p><strong>Description :</strong> " . htmlspecialchars($event['event_description']) . "</p>";

            // Bouton d'inscription
            if (isset($_SESSION['user_id'])) {
                echo '<button type="button" class="btn btn-primary" onclick="registerToEvent(' . $event['id'] . ')">S\'inscrire à cet événement</button>';
            } else {
                echo '<p class="text-danger">Vous devez être connecté pour vous inscrire à cet événement.</p>';
            }
        } else {
            echo "Événement non trouvé.";
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

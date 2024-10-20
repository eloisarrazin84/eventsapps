<?php
session_start();
if (isset($_GET['id'])) {
    $eventId = $_GET['id'];

    // Connexion à la base de données
    $servername = "localhost";
    $username = "root";  
    $password = "Lipton2019!";
    $dbname = "outdoorsec";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les détails de l'événement
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            echo '<h3>' . htmlspecialchars($event['event_name']) . '</h3>';
            echo '<p>Date: ' . htmlspecialchars($event['event_date']) . '</p>';
            echo '<p>Lieu: ' . htmlspecialchars($event['event_location']) . '</p>';
            echo '<p>Description: ' . htmlspecialchars($event['event_description']) . '</p>';
            echo '<img src="' . htmlspecialchars($event['event_image']) . '" alt="Event Image" style="width:100%; height:auto;">';

            // Afficher le bouton d'inscription si l'utilisateur est connecté
            if (isset($_SESSION['user_id'])) {
                echo '<form method="POST" action="register_event.php">';
                echo '<input type="hidden" name="event_id" value="' . $eventId . '">';
                echo '<button type="submit" class="btn btn-primary mt-3">S\'inscrire à cet événement</button>';
                echo '</form>';
            } else {
                echo '<p class="text-danger mt-3">Vous devez être connecté pour vous inscrire à cet événement.</p>';
            }
        } else {
            echo 'Événement non trouvé.';
        }

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

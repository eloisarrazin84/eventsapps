<?php
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
        } else {
            echo 'Événement non trouvé.';
        }

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

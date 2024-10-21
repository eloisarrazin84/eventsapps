<?php
$event_id = $_GET['id'];

// Connexion à la base de données
$servername = "localhost";
$username_db = "root";  
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails de l'événement
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->bindParam(':id', $event_id);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        echo "<h5>" . htmlspecialchars($event['event_name']) . "</h5>";
        echo "<p><strong>Date :</strong> " . htmlspecialchars($event['event_date']) . "</p>";
        echo "<p><strong>Lieu :</strong> " . htmlspecialchars($event['event_location']) . "</p>";
        echo "<p><strong>Description :</strong> " . htmlspecialchars($event['event_description']) . "</p>";
        
        // Bouton d'inscription
        echo '<button id="registerButton" class="btn btn-primary" onclick="registerForEvent(' . htmlspecialchars($event_id) . ')">S\'inscrire à cet événement</button>';
    } else {
        echo "Événement non trouvé.";
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
<script>
    function registerForEvent(eventId) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "register_event.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert(xhr.responseText);
            }
        };
        xhr.send("event_id=" + eventId);
    }
</script>

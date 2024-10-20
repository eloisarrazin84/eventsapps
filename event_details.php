<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['id'])) {
        $eventId = $_GET['id'];

        // Récupérer les détails de l'événement, y compris la description
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo "Événement non trouvé.";
            exit();
        }

        // Vérifier si l'utilisateur est déjà inscrit
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT * FROM event_user_assignments WHERE event_id = :event_id AND user_id = :user_id");
            $stmt->bindParam(':event_id', $eventId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $alreadyRegistered = $stmt->rowCount() > 0;
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        if (!$alreadyRegistered) {
            $stmt = $conn->prepare("INSERT INTO event_user_assignments (event_id, user_id) VALUES (:event_id, :user_id)");
            $stmt->bindParam(':event_id', $eventId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $alreadyRegistered = true;
        }
    }

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'événement</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fc;
        }
        .event-container {
            max-width: 800px;
            margin: 40px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .event-image {
            width: 100%;
            height: auto;
            max-height: 450px;
            object-fit: cover;
            border-radius: 8px;
        }
        .event-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .event-info {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 10px;
        }
        .event-info strong {
            color: #333;
        }
        .event-description {
            font-size: 1rem;
            line-height: 1.6;
            margin-top: 20px;
            color: #444;
        }
        .register-btn {
            margin-top: 20px;
            text-align: center;
        }
        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <?php if ($event): ?>
    <div class="event-container">
        <div class="event-title">
            <?php echo htmlspecialchars($event['event_name']); ?>
        </div>
        <p class="event-info"><strong>Date :</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
        <p class="event-info"><strong>Lieu :</strong> <?php echo htmlspecialchars($event['event_location']); ?></p>

        <?php if (!empty($event['event_image'])): ?>
            <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>" class="event-image">
        <?php endif; ?>

        <div class="event-description">
            <p><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
        </div>

        <!-- Carte OpenStreetMap -->
        <div id="map"></div>

        <div class="register-btn">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($alreadyRegistered): ?>
                    <p class="text-success">Vous êtes déjà inscrit à cet événement.</p>
                <?php else: ?>
                    <form method="POST" action="">
                        <button type="submit" class="btn btn-primary">S'inscrire à cet événement</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-danger">Vous devez être connecté pour vous inscrire à cet événement.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
        <p class="text-center">Aucun événement trouvé.</p>
    <?php endif; ?>
</div>

<!-- OpenStreetMap avec Leaflet -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script>
    var map = L.map('map').setView([43.7, 7.26], 13); // Centrer la carte sur Nice par exemple

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var marker = L.marker([43.7, 7.26]).addTo(map) // Remplacez par les coordonnées correctes
        .bindPopup('<b><?php echo htmlspecialchars($event['event_location']); ?></b>')
        .openPopup();
</script>
</body>
</html>

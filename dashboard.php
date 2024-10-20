<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";  
$password = "Lipton2019!";
$dbname = "outdoorsec";

try {
    // Connexion avec PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les événements à venir avec leurs latitudes et longitudes
    $stmt = $conn->prepare("SELECT id, event_name, event_date, event_location, event_image, lat, lng FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
    $stmt->execute();
    $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Événements à venir</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            background-color: #f7f9fc;
        }
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .event-card {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .event-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .event-card-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            text-align: center;
            padding: 5px;
            font-size: 1rem;
            font-weight: bold;
        }
        #map {
            height: 400px;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?> <!-- Menu inclusion -->

<div class="container">
    <h1 class="mt-5">Événements à venir</h1>
    
    <!-- Grille des événements -->
    <div class="event-grid">
        <?php foreach ($upcomingEvents as $event): ?>
        <a href="event_details.php?id=<?php echo $event['id']; ?>" class="event-card">
            <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
            <div class="event-card-title"><?php echo htmlspecialchars($event['event_name']); ?></div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Section de la carte -->
    <h2 class="mt-5">Carte des événements</h2>
    <div id="map"></div>
</div>

<!-- JavaScript pour la carte -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([46.603354, 1.888334], 6);  // Vue centrée sur la France

    // Ajout du fond de carte OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Ajout des marqueurs pour chaque événement
    var events = <?php echo json_encode($upcomingEvents); ?>;
    events.forEach(function(event) {
        if (event.lat && event.lng) {
            L.marker([event.lat, event.lng]).addTo(map)
                .bindPopup("<strong>" + event.event_name + "</strong><br>" + event.event_location + "<br>Date : " + event.event_date);
        }
    });
</script>

</body>
</html>

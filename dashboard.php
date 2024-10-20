<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";  // Your database username
$password = "Lipton2019!";  // Your database password
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch upcoming events with their locations and images
    $stmt = $conn->prepare("SELECT id, event_name, event_location, event_image, lat, lon FROM events WHERE event_date >= CURDATE()");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        .event-grid {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .event-card {
            width: 30%;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: 0.3s;
        }
        .event-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .event-card-title {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            font-size: 1.2rem;
        }
        #map {
            height: 400px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Événements à venir</h1>
    <div class="event-grid">
        <?php foreach ($events as $event): ?>
        <div class="event-card">
            <a href="event_details.php?id=<?php echo $event['id']; ?>">
                <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
                <div class="event-card-title"><?php echo htmlspecialchars($event['event_name']); ?></div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Map -->
    <h2 class="mt-5">Carte des événements</h2>
    <div id="map"></div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    // Initialize the map
    var map = L.map('map').setView([46.603354, 1.888334], 6); // Center of France

    // Add the OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add markers for each event from PHP
    var events = [
        <?php foreach ($events as $event): ?>
        {
            name: '<?php echo addslashes($event['event_name']); ?>',
            location: '<?php echo addslashes($event['event_location']); ?>',
            lat: <?php echo $event['lat']; ?>,
            lon: <?php echo $event['lon']; ?>
        },
        <?php endforeach; ?>
    ];

    // Loop through the events and add markers to the map
    events.forEach(function(event) {
        L.marker([event.lat, event.lon]).addTo(map)
            .bindPopup("<strong>" + event.name + "</strong><br>" + event.location);
    });
</script>

</body>
</html>

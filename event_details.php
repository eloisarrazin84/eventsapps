<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";  // Replace with your database username
$password = "Lipton2019!";  // Replace with your database password
$dbname = "outdoorsec";

try {
    // Connexion with PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if an event ID is passed
    if (isset($_GET['id'])) {
        $eventId = $_GET['id'];

        // Fetch event details
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo "Event not found.";
            exit();
        }

        // Check if the user is already registered
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT * FROM event_user_assignments WHERE event_id = :event_id AND user_id = :user_id");
            $stmt->bindParam(':event_id', $eventId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $alreadyRegistered = $stmt->rowCount() > 0;
        }
    }

    // Handle event registration
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
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
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
            height: 300px;
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
        <p class="event-info"><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
        <p class="event-info"><strong>Location:</strong> <?php echo htmlspecialchars($event['event_location']); ?></p>

        <?php if (!empty($event['event_image'])): ?>
            <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>" class="event-image">
        <?php endif; ?>

        <div class="event-description">
            <p><?php echo htmlspecialchars($event['event_description']); ?></p>
        </div>

        <!-- OpenStreetMap -->
        <div id="map"></div>

        <div class="register-btn">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($alreadyRegistered): ?>
                    <p class="text-success">You are already registered for this event.</p>
                <?php else: ?>
                    <form method="POST" action="">
                        <button type="submit" class="btn btn-primary">Register for this event</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-danger">You need to be logged in to register for this event.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
        <p class="text-center">No event found.</p>
    <?php endif; ?>
</div>

<script>
    var lat = <?php echo htmlspecialchars($event['lat']); ?>;
    var lng = <?php echo htmlspecialchars($event['lng']); ?>;
    
    // Initialize the map with the event's coordinates
    var map = L.map('map').setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add a marker at the event's location
    var marker = L.marker([lat, lng]).addTo(map)
        .bindPopup("<?php echo htmlspecialchars($event['event_location']); ?>")
        .openPopup();
</script>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

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

    // Récupérer les événements à venir
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
            cursor: pointer;
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
            height: 400px;  /* Adjusted height */
            width: 100%;
            margin-top: 30px;
            max-width: 1200px; /* Corresponds to the event grid width */
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?> <!-- Menu inclusion -->

<div class="container">
    <h1 class="mt-5">Événements à venir</h1>
    
    <div class="event-grid">
        <?php foreach ($upcomingEvents as $event): ?>
            <div class="event-card" data-id="<?php echo $event['id']; ?>" data-toggle="modal" data-target="#eventModal">
                <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
                <div class="event-card-title"><?php echo htmlspecialchars($event['event_name']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Détails de l'événement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Contenu de l'événement sera injecté ici -->
                <div id="eventDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Section de la carte -->
<div id="map"></div>

<!-- Bootstrap JS et jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    // Carte Leaflet
var map = L.map('map').setView([46.603354, 1.888334], 6);  // Centré sur la France
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
}).addTo(map);

// Ajouter les événements sur la carte
var events = <?php echo json_encode($upcomingEvents); ?>;
console.log(events); // Debugging: voir les événements et leurs coordonnées

events.forEach(function(event) {
    if (event.lat && event.lng) {
        L.marker([event.lat, event.lng]).addTo(map)
            .bindPopup("<strong>" + event.event_name + "</strong><br>" + event.event_location + "<br>Date : " + event.event_date);
    } else {
        console.error("Pas de coordonnées pour l'événement : " + event.event_name);
    }
});

    // Gestion du clic sur les événements
    $(document).ready(function(){
        $('.event-card').on('click', function(){
            var eventId = $(this).data('id');
            
            // Requête AJAX pour obtenir les détails de l'événement
            $.ajax({
                url: 'event_details_ajax.php',  // Page pour récupérer les détails
                method: 'GET',
                data: { id: eventId },
                success: function(response) {
                    $('#eventDetails').html(response); // Insérer les détails dans le modal

                    // Ajouter un listener pour le bouton "S'inscrire"
                    $('#registerButton').on('click', function() {
                        $.ajax({
                            url: 'register_event.php',
                            method: 'POST',
                            data: { event_id: eventId },
                            success: function(response) {
                                alert(response); // Affiche le message de succès ou d'erreur
                            }
                        });
                    });
                }
            });
        });
    });
</script>

</body>
</html>

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

    // Limite initiale des événements chargés
    $limit = 10;
    $stmt = $conn->prepare("SELECT id, event_name, event_date, event_location, event_image, lat, lng, registration_deadline 
                            FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
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
    <!-- Ajout du style Bootstrap et Leaflet pour la carte -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    
    <!-- Styles personnalisés -->
    <style>
        body {
            background-color: #f7f9fc; /* Couleur d'arrière-plan légère */
        }

        /* Disposition des cartes d'événements */
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* S'adapte à l'espace disponible */
            gap: 20px; /* Espacement entre les cartes */
            margin-top: 20px;
        }

        /* Style des cartes d'événements */
        .event-card {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Ombre légère */
            cursor: pointer;
            transition: transform 0.3s ease; /* Effet de zoom */
        }

        /* Image des cartes */
        .event-card img {
            width: 100%;
            height: 180px; /* Hauteur uniforme pour les images */
            object-fit: cover; /* Couvre l'espace disponible sans déformer */
            border-radius: 15px 15px 0 0; /* Bords arrondis en haut */
        }

        /* Effet de zoom sur hover */
        .event-card:hover {
            transform: scale(1.05); /* Légère mise en valeur au survol */
        }

        /* Titre des événements */
        .event-card-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7); /* Fond semi-transparent */
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 0 0 15px 15px; /* Arrondi en bas */
        }

        /* Style des filtres de recherche */
        .filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px; /* Espacement entre les champs */
            margin-bottom: 20px;
        }

        /* Champs de recherche */
        .filters input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            max-width: 300px; /* Limite de largeur pour les champs */
        }

        /* Style de la carte */
        #map {
            height: 400px;
            width: 100%;
            margin-top: 30px;
            max-width: 1200px; /* Largeur max */
            margin-left: auto;
            margin-right: auto;
            border-radius: 15px; /* Arrondir la carte */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Ombre légère */
        }

        /* Style pour la section des participants */
        .participants {
            background: #f8f9fa;
            padding: 10px;
            text-align: center;
            font-size: 0.9rem;
            border-radius: 0 0 15px 15px; /* Arrondir en bas */
        }
    </style>
</head>
<body>

<!-- Inclure le menu -->
<?php include 'menu.php'; ?>

<!-- Contenu principal -->
<div class="container">
    <h1 class="mt-5">Événements à venir</h1>

    <!-- Filtres de recherche par lieu et date -->
    <div class="filters">
        <input type="text" id="filterLocation" placeholder="Filtrer par lieu">
        <input type="date" id="filterDate" placeholder="Filtrer par date">
    </div>
    
    <!-- Grille des événements -->
    <div class="event-grid">
        <?php foreach ($upcomingEvents as $event): ?>
            <div class="event-card" data-id="<?php echo $event['id']; ?>" data-toggle="modal" data-target="#eventModal">
                <!-- Affichage de l'image de l'événement -->
                <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
                <!-- Titre de l'événement -->
                <div class="event-card-title"><?php echo htmlspecialchars($event['event_name']); ?></div>
                <!-- Nombre de participants et date limite -->
                <div class="participants">
                    <?php
                    $stmtParticipants = $conn->prepare("SELECT COUNT(*) FROM event_user_assignments WHERE event_id = :event_id");
                    $stmtParticipants->bindParam(':event_id', $event['id']);
                    $stmtParticipants->execute();
                    $numParticipants = $stmtParticipants->fetchColumn();
                    ?>
                    <p>Participants inscrits : <?= $numParticipants ?></p>
                    <p>Date limite d'inscription : <?= $event['registration_deadline'] ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal pour afficher les détails de l'événement -->
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
                <div id="eventDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Carte des événements -->
<div id="map"></div>

<!-- Scripts Bootstrap, jQuery et Leaflet -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    // Initialisation de la carte Leaflet centrée sur la France
    var map = L.map('map').setView([46.603354, 1.888334], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Ajouter les événements à la carte
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

    // Gérer les événements cliqués
    $(document).ready(function(){
        $('.event-card').on('click', function(){
            var eventId = $(this).data('id');
            
            // Requête AJAX pour obtenir les détails
            $.ajax({
                url: 'event_details_ajax.php',
                method: 'GET',
                data: { id: eventId },
                success: function(response) {
                    $('#eventDetails').html(response);
                    $('#registerButton').on('click', function() {
                        $.ajax({
                            url: 'register_event.php',
                            method: 'POST',
                            data: { event_id: eventId },
                            success: function(response) {
                                alert(response);
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

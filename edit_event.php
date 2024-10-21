<?php
session_start();

// Vérification que l'utilisateur est bien administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";  
$password = "Lipton2019!";  
$dbname = "outdoorsec";

if (isset($_GET['id'])) {
    $eventId = $_GET['id'];

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les détails de l'événement
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo "Événement non trouvé.";
            exit();
        }

        // Mise à jour de l'événement
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $event_name = $_POST['event_name'];
            $event_date = $_POST['event_date'];
            $event_location = $_POST['event_location'];
            $event_description = $_POST['event_description'];

            $event_image = $event['event_image'];  // Conserver l'image précédente si non modifiée

            // Gestion de l'upload de l'image
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
                $image_name = basename($_FILES['event_image']['name']);
                $image_path = 'uploads/' . $image_name;

                // Déplacer l'image uploadée
                if (move_uploaded_file($_FILES['event_image']['tmp_name'], $image_path)) {
                    $event_image = $image_path;
                } else {
                    echo "Erreur lors du déplacement du fichier.";
                }
            }

            // Utiliser l'API de géocodage Nominatim pour obtenir les coordonnées
            $geocode_url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($event_location);
            $geocode_data = file_get_contents($geocode_url);
            $geocode = json_decode($geocode_data, true);

            if (!empty($geocode)) {
                $lat = $geocode[0]['lat'];
                $lng = $geocode[0]['lon'];

                // Mise à jour des informations de l'événement avec les coordonnées
                $stmt = $conn->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_location = :event_location, event_description = :event_description, event_image = :event_image, lat = :lat, lng = :lng WHERE id = :id");
                $stmt->bindParam(':event_name', $event_name);
                $stmt->bindParam(':event_date', $event_date);
                $stmt->bindParam(':event_location', $event_location);
                $stmt->bindParam(':event_description', $event_description);
                $stmt->bindParam(':event_image', $event_image);
                $stmt->bindParam(':lat', $lat);
                $stmt->bindParam(':lng', $lng);
                $stmt->bindParam(':id', $eventId);
                $stmt->execute();

                header("Location: manage_events.php");
                exit();
            } else {
                echo "Erreur : Impossible de récupérer les coordonnées pour l'adresse fournie.";
            }
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "Aucun événement sélectionné.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'événement</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Inclusion de Leaflet.js pour la carte -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    
    <script>
        // Fonction d'initialisation de la carte et autocomplétion
        function initMap() {
            const map = L.map('map').setView([48.8566, 2.3522], 5);  // Vue centrée sur Paris

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const searchInput = document.getElementById('event_location');
            const searchResults = document.getElementById('search-results');

            searchInput.addEventListener('input', function() {
                const query = searchInput.value;
                if (query.length > 2) {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        data.forEach(item => {
                            const option = document.createElement('div');
                            option.className = 'search-item';
                            option.textContent = item.display_name;
                            option.onclick = function() {
                                searchInput.value = item.display_name;
                                map.setView([item.lat, item.lon], 12);
                                searchResults.innerHTML = '';
                            };
                            searchResults.appendChild(option);
                        });
                    });
                }
            });
        }
    </script>
    <style>
        #search-results {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #ccc;
            margin-top: 10px;
        }
        .search-item {
            padding: 5px;
            cursor: pointer;
        }
        .search-item:hover {
            background-color: #f0f0f0;
        }
        #map {
            height: 300px;
            margin-top: 20px;
        }
    </style>
</head>
<body onload="initMap()">
<div class="container">
    <h1 class="mt-5">Modifier l'événement</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="event_name">Nom de l'événement</label>
            <input type="text" class="form-control" id="event_name" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="event_date">Date de l'événement</label>
            <input type="date" class="form-control" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="event_location">Lieu de l'événement</label>
            <input type="text" class="form-control" id="event_location" name="event_location" value="<?php echo htmlspecialchars($event['event_location']); ?>">
            <div id="search-results"></div>
        </div>
        <div id="map"></div>
        <div class="form-group">
            <label for="event_description">Description de l'événement</label>
            <textarea class="form-control" id="event_description" name="event_description"><?php echo htmlspecialchars($event['event_description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="event_image">Image de l'événement (laisser vide si inchangée)</label>
            <input type="file" class="form-control-file" id="event_image" name="event_image">
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="manage_events.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>

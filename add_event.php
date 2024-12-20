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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_location = $_POST['event_location'];
    $event_description = $_POST['event_description'];
    $event_image = null;
    $registration_deadline = $_POST['registration_deadline']; // Ajout du champ de date limite d'inscription

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

    // Appel à l'API Nominatim pour récupérer les coordonnées de l'adresse
    $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($event_location) . "&format=json&limit=1";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!empty($data)) {
        $lat = $data[0]['lat'];
        $lng = $data[0]['lon'];

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Insertion de l'événement avec les coordonnées, l'image, et la date limite d'inscription
            $stmt = $conn->prepare("INSERT INTO events (event_name, event_date, event_location, event_description, event_image, lat, lng, registration_deadline) 
                                    VALUES (:event_name, :event_date, :event_location, :event_description, :event_image, :lat, :lng, :registration_deadline)");
            $stmt->bindParam(':event_name', $event_name);
            $stmt->bindParam(':event_date', $event_date);
            $stmt->bindParam(':event_location', $event_location);
            $stmt->bindParam(':event_description', $event_description);
            $stmt->bindParam(':event_image', $event_image);
            $stmt->bindParam(':lat', $lat);
            $stmt->bindParam(':lng', $lng);
            $stmt->bindParam(':registration_deadline', $registration_deadline);
            $stmt->execute();

            header("Location: manage_events.php");
            exit();

        } catch(PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    } else {
        echo "Erreur : adresse non trouvée.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un événement</title>
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
        body {
            background-color: #f0f2f5;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        #search-results {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #ccc;
            margin-top: 5px;
            border-radius: 5px;
            background: #fff;
        }
        .search-item {
            padding: 10px;
            cursor: pointer;
        }
        .search-item:hover {
            background-color: #f0f0f0;
        }
        #map {
            height: 300px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .form-group label {
            font-weight: bold;
        }
        .btn {
            width: 48%;
            margin-top: 10px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .btn-secondary:hover {
            background-color: #565e64;
            border-color: #565e64;
        }
    </style>
</head>
<body onload="initMap()">
<div class="container">
    <h1 class="mt-3 mb-4 text-center">Ajouter un événement</h1>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="event_name">Nom de l'événement</label>
            <input type="text" class="form-control" id="event_name" name="event_name" required>
        </div>
        <div class="form-group">
            <label for="event_date">Date de l'événement</label>
            <input type="date" class="form-control" id="event_date" name="event_date" required>
        </div>
        <div class="form-group">
            <label for="registration_deadline">Date limite d'inscription</label>
            <input type="date" class="form-control" id="registration_deadline" name="registration_deadline" required>
        </div>
        <div class="form-group">
            <label for="event_location">Lieu de l'événement</label>
            <input type="text" class="form-control" id="event_location" name="event_location">
            <div id="search-results"></div>
        </div>
        <div id="map"></div>
        <div class="form-group mt-4">
            <label for="event_description">Description de l'événement</label>
            <textarea class="form-control" id="event_description" name="event_description" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label for="event_image">Image de l'événement</label>
            <input type="file" class="form-control-file" id="event_image" name="event_image">
        </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Ajouter</button>
            <a href="manage_events.php" class="btn btn-secondary">Retour</a>
        </div>
    </form>
</div>
</body>
</html>

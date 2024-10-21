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

        // Récupérer les champs supplémentaires de l'événement
        $stmt = $conn->prepare("SELECT * FROM event_fields WHERE event_id = :event_id");
        $stmt->bindParam(':event_id', $eventId);
        $stmt->execute();
        $eventFields = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mise à jour de l'événement
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $event_name = $_POST['event_name'];
            $event_date = $_POST['event_date'];
            $event_location = $_POST['event_location'];
            $event_description = $_POST['event_description'];
            $registration_deadline = $_POST['registration_deadline'];
            $lat = $_POST['lat'];
            $lng = $_POST['lng'];

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

            // Mise à jour des informations de l'événement
            $stmt = $conn->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_location = :event_location, 
                                    event_description = :event_description, event_image = :event_image, lat = :lat, lng = :lng, 
                                    registration_deadline = :registration_deadline WHERE id = :id");
            $stmt->bindParam(':event_name', $event_name);
            $stmt->bindParam(':event_date', $event_date);
            $stmt->bindParam(':event_location', $event_location);
            $stmt->bindParam(':event_description', $event_description);
            $stmt->bindParam(':event_image', $event_image);
            $stmt->bindParam(':lat', $lat);
            $stmt->bindParam(':lng', $lng);
            $stmt->bindParam(':registration_deadline', $registration_deadline);
            $stmt->bindParam(':id', $eventId);
            $stmt->execute();

            // Suppression des champs existants
            if (isset($_POST['delete_field'])) {
                foreach ($_POST['delete_field'] as $fieldId) {
                    $stmt = $conn->prepare("DELETE FROM event_fields WHERE id = :field_id");
                    $stmt->bindParam(':field_id', $fieldId);
                    $stmt->execute();
                }
            }

            // Ajout ou mise à jour des champs supplémentaires
            if (isset($_POST['field_name']) && isset($_POST['field_type'])) {
                for ($i = 0; $i < count($_POST['field_name']); $i++) {
                    $field_name = $_POST['field_name'][$i];
                    $field_type = $_POST['field_type'][$i];
                    $field_options = isset($_POST['field_options'][$i]) ? $_POST['field_options'][$i] : null;

                    if (isset($_POST['field_id'][$i])) {
                        // Mise à jour du champ existant
                        $field_id = $_POST['field_id'][$i];
                        $stmt = $conn->prepare("UPDATE event_fields SET field_name = :field_name, field_type = :field_type, field_options = :field_options WHERE id = :field_id");
                        $stmt->bindParam(':field_name', $field_name);
                        $stmt->bindParam(':field_type', $field_type);
                        $stmt->bindParam(':field_options', $field_options);
                        $stmt->bindParam(':field_id', $field_id);
                        $stmt->execute();
                    } else {
                        // Ajout d'un nouveau champ
                        $stmt = $conn->prepare("INSERT INTO event_fields (event_id, field_name, field_type, field_options) VALUES (:event_id, :field_name, :field_type, :field_options)");
                        $stmt->bindParam(':event_id', $eventId);
                        $stmt->bindParam(':field_name', $field_name);
                        $stmt->bindParam(':field_type', $field_type);
                        $stmt->bindParam(':field_options', $field_options);
                        $stmt->execute();
                    }
                }
            }

            header("Location: manage_events.php");
            exit();
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
                        if (data.length === 0) {
                            alert("Impossible de récupérer les coordonnées pour l'adresse fournie. Veuillez vérifier l'adresse.");
                        }
                        data.forEach(item => {
                            const option = document.createElement('div');
                            option.className = 'search-item';
                            option.textContent = item.display_name;
                            option.onclick = function() {
                                searchInput.value = item.display_name;
                                document.getElementById('lat').value = item.lat;
                                document.getElementById('lng').value = item.lon;
                                map.setView([item.lat, item.lon], 12);
                                searchResults.innerHTML = '';
                            };
                            searchResults.appendChild(option);
                        });
                    });
                }
            });
        }

        // Ajouter ou supprimer des champs supplémentaires
        function addField() {
            const container = document.getElementById('fields-container');
            const fieldDiv = document.createElement('div');
            fieldDiv.classList.add('field');

            const fieldHTML = `
                <div class="form-group">
                    <label for="field_name">Nom du champ</label>
                    <input type="text" class="form-control" name="field_name[]" placeholder="Nom du champ" required>
                </div>
                <div class="form-group">
                    <label for="field_type">Type de champ</label>
                    <select class="form-control field-type" name="field_type[]" onchange="toggleOptions(this)">
                        <option value="text">Texte</option>
                        <option value="number">Nombre</option>
                        <option value="date">Date</option>
                        <option value="checkbox">Case à cocher</option>
                        <option value="multiple">Choix multiple</option>
                    </select>
                </div>
                <div class="form-group field-options" style="display: none;">
                    <label for="field_options">Options (séparées par une virgule)</label>
                    <input type="text" class="form-control" name="field_options[]" placeholder="Exemple : Option 1, Option 2, Option 3">
                </div>
                <button type="button" class="btn btn-danger mt-2" onclick="removeField(this)">Supprimer ce champ</button>
            `;

            fieldDiv.innerHTML = fieldHTML;
            container.appendChild(fieldDiv);
        }

        function removeField(button) {
            button.parentElement.remove();
        }

        function toggleOptions(selectElement) {
            const optionsDiv = selectElement.parentElement.nextElementSibling;
            if (selectElement.value === 'checkbox' || selectElement.value === 'multiple') {
                optionsDiv.style.display = 'block';
            } else {
                optionsDiv.style.display = 'none';
            }
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
            <label for="registration_deadline">Date limite d'inscription</label>
            <input type="date" class="form-control" id="registration_deadline" name="registration_deadline" value="<?php echo htmlspecialchars($event['registration_deadline']); ?>" required>
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
            <label for="lat">Latitude</label>
            <input type="text" class="form-control" id="lat" name="lat" value="<?php echo htmlspecialchars($event['lat']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="lng">Longitude</label>
            <input type="text" class="form-control" id="lng" name="lng" value="<?php echo htmlspecialchars($event['lng']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="event_image">Image de l'événement (laisser vide si inchangée)</label>
            <input type="file" class="form-control-file" id="event_image" name="event_image">
        </div>
        
        <h3>Champs supplémentaires</h3>
        <div id="fields-container">
            <?php foreach ($eventFields as $field): ?>
                <div class="field">
                    <input type="hidden" name="field_id[]" value="<?php echo $field['id']; ?>">
                    <div class="form-group">
                        <label for="field_name">Nom du champ</label>
                        <input type="text" class="form-control" name="field_name[]" value="<?php echo htmlspecialchars($field['field_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="field_type">Type de champ</label>
                        <select class="form-control field-type" name="field_type[]" onchange="toggleOptions(this)">
                            <option value="text" <?php if ($field['field_type'] == 'text') echo 'selected'; ?>>Texte</option>
                            <option value="number" <?php if ($field['field_type'] == 'number') echo 'selected'; ?>>Nombre</option>
                            <option value="date" <?php if ($field['field_type'] == 'date') echo 'selected'; ?>>Date</option>
                            <option value="checkbox" <?php if ($field['field_type'] == 'checkbox') echo 'selected'; ?>>Case à cocher</option>
                            <option value="multiple" <?php if ($field['field_type'] == 'multiple') echo 'selected'; ?>>Choix multiple</option>
                        </select>
                    </div>
                    <div class="form-group field-options" <?php if ($field['field_type'] != 'checkbox' && $field['field_type'] != 'multiple') echo 'style="display: none;"'; ?>>
                        <label for="field_options">Options (séparées par une virgule)</label>
                        <input type="text" class="form-control" name="field_options[]" value="<?php echo htmlspecialchars($field['field_options']); ?>">
                    </div>
                    <button type="button" class="btn btn-danger mt-2" onclick="removeField(this)">Supprimer ce champ</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-success mt-3" onclick="addField()">Ajouter un champ</button>

        <button type="submit" class="btn btn-primary mt-4">Modifier</button>
        <a href="manage_events.php" class="btn btn-secondary mt-4">Retour</a>
    </form>
</div>
</body>
</html>

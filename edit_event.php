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

        // Récupérer les champs supplémentaires
        $stmt = $conn->prepare("SELECT * FROM event_fields WHERE event_id = :event_id");
        $stmt->bindParam(':event_id', $eventId);
        $stmt->execute();
        $eventFields = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

            // Mise à jour des champs supplémentaires
            if (isset($_POST['field_id'])) {
                foreach ($_POST['field_id'] as $key => $fieldId) {
                    if (isset($_POST['delete_field']) && in_array($fieldId, $_POST['delete_field'])) {
                        // Supprimer le champ supplémentaire
                        $stmt = $conn->prepare("DELETE FROM event_fields WHERE id = :field_id");
                        $stmt->bindParam(':field_id', $fieldId);
                        $stmt->execute();
                    } else {
                        // Mettre à jour le champ supplémentaire
                        $field_label = $_POST['field_label'][$key];
                        $field_type = $_POST['field_type'][$key];
                        $stmt = $conn->prepare("UPDATE event_fields SET label = :label, field_type = :field_type WHERE id = :field_id");
                        $stmt->bindParam(':label', $field_label);
                        $stmt->bindParam(':field_type', $field_type);
                        $stmt->bindParam(':field_id', $fieldId);
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
    </script>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
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
    <h1 class="mt-3 mb-4 text-center">Modifier l'événement</h1>

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
        <div class="form-group mt-4">
            <label for="event_description">Description de l'événement</label>
            <textarea class="form-control" id="event_description" name="event_description" rows="4"><?php echo htmlspecialchars($event['event_description']); ?></textarea>
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

        <!-- Champs supplémentaires -->
        <h4 class="mt-5">Champs supplémentaires</h4>
        <div id="additional-fields">
            <?php foreach ($eventFields as $field) : ?>
                <div class="form-group additional-field">
                    <label>Nom du champ</label>
                    <input type="text" name="field_label[]" class="form-control" value="<?php echo htmlspecialchars($field['label']); ?>" required>
                    <label>Type de champ</label>
                    <select name="field_type[]" class="form-control">
                        <option value="text" <?php echo ($field['field_type'] == 'text') ? 'selected' : ''; ?>>Texte</option>
                        <option value="number" <?php echo ($field['field_type'] == 'number') ? 'selected' : ''; ?>>Nombre</option>
                        <option value="date" <?php echo ($field['field_type'] == 'date') ? 'selected' : ''; ?>>Date</option>
                        <option value="checkbox" <?php echo ($field['field_type'] == 'checkbox') ? 'selected' : ''; ?>>Case à cocher</option>
                        <option value="multiple" <?php echo ($field['field_type'] == 'multiple') ? 'selected' : ''; ?>>Choix multiple</option>
                    </select>
                    <input type="hidden" name="field_id[]" value="<?php echo $field['id']; ?>">
                    <div class="mt-2">
                        <button type="button" class="btn btn-danger remove-field">Supprimer ce champ</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" id="add-field" class="btn btn-success mt-3">Ajouter un champ</button>

        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary">Modifier</button>
            <a href="manage_events.php" class="btn btn-secondary">Retour</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('add-field').addEventListener('click', function () {
            const additionalFields = document.getElementById('additional-fields');
            const fieldHTML = `
                <div class="form-group additional-field">
                    <label>Nom du champ</label>
                    <input type="text" name="field_label[]" class="form-control" required>
                    <label>Type de champ</label>
                    <select name="field_type[]" class="form-control">
                        <option value="text">Texte</option>
                        <option value="number">Nombre</option>
                        <option value="date">Date</option>
                        <option value="checkbox">Case à cocher</option>
                        <option value="multiple">Choix multiple</option>
                    </select>
                    <div class="mt-2">
                        <button type="button" class="btn btn-danger remove-field">Supprimer ce champ</button>
                    </div>
                </div>
            `;
            additionalFields.insertAdjacentHTML('beforeend', fieldHTML);
        });

        document.getElementById('additional-fields').addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-field')) {
                const fieldGroup = event.target.closest('.additional-field');
                fieldGroup.remove();
            }
        });
    });
</script>
</body>
</html>

<?php
session_start();

// Vérification si l'utilisateur est administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";  // Remplacez par votre utilisateur de base de données
$password = "Lipton2019!";  // Remplacez par votre mot de passe de base de données
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['id'])) {
        $eventId = $_GET['id'];

        // Récupérer les détails de l'événement
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo "Événement non trouvé.";
            exit();
        }

        // Si le formulaire est soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $event_name = $_POST['event_name'];
            $event_date = $_POST['event_date'];
            $event_location = $_POST['event_location'];
            $event_description = $_POST['event_description'];
            $event_image = $event['event_image']; // Garder l'image existante par défaut

            // Gestion de l'upload d'une nouvelle image
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
                $image_name = basename($_FILES['event_image']['name']);
                $image_path = 'uploads/' . $image_name;

                if (move_uploaded_file($_FILES['event_image']['tmp_name'], $image_path)) {
                    $event_image = $image_path; // Met à jour le chemin de l'image
                }
            }

            // Mise à jour des informations dans la base de données
            $stmt = $conn->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_location = :event_location, event_description = :event_description, event_image = :event_image WHERE id = :id");
            $stmt->bindParam(':event_name', $event_name);
            $stmt->bindParam(':event_date', $event_date);
            $stmt->bindParam(':event_location', $event_location);
            $stmt->bindParam(':event_description', $event_description);
            $stmt->bindParam(':event_image', $event_image);
            $stmt->bindParam(':id', $eventId);
            $stmt->execute();

            header("Location: manage_events.php");
            exit();
        }
    } else {
        echo "Aucun événement sélectionné.";
        exit();
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'événement</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script> <!-- Map integration -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 300px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mt-5">Modifier l'événement</h1>

    <form method="POST" action="" enctype="multipart/form-data">
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
        </div>

        <div id="map"></div> <!-- Map placeholder -->

        <div class="form-group mt-3">
            <label for="event_description">Description de l'événement</label>
            <textarea class="form-control" id="event_description" name="event_description" rows="4"><?php echo htmlspecialchars($event['event_description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="event_image">Image de l'événement</label><br>
            <?php if (!empty($event['event_image'])): ?>
                <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="Event Image" style="max-width: 200px;"><br>
            <?php endif; ?>
            <input type="file" class="form-control-file" id="event_image" name="event_image">
        </div>

        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="manage_events.php" class="btn btn-secondary">Retour</a>
    </form>
</div>

<!-- Script for map initialization -->
<script>
    var map = L.map('map').setView([43.7034, 7.2663], 13); // Example coordinates (Nice)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var marker = L.marker([43.7034, 7.2663]).addTo(map).bindPopup("Lieu de l'événement");
</script>

</body>
</html>

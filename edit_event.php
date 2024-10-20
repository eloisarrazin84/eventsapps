<?php
session_start();

// Vérification de l'administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";  
$password = "Lipton2019!";
$dbname = "outdoorsec";
$eventId = $_GET['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails de l'événement
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->bindParam(':id', $eventId);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $event_name = $_POST['event_name'];
        $event_date = $_POST['event_date'];
        $event_location = $_POST['event_location'];
        $lat = $_POST['lat'];
        $lng = $_POST['lng'];

        // Gestion de l'image
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
            $image_name = basename($_FILES['event_image']['name']);
            $image_path = 'uploads/' . $image_name;
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $image_path)) {
                $stmt = $conn->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_location = :event_location, lat = :lat, lng = :lng, event_image = :event_image WHERE id = :id");
                $stmt->bindParam(':event_image', $image_path);
            }
        } else {
            $stmt = $conn->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_location = :event_location, lat = :lat, lng = :lng WHERE id = :id");
        }

        $stmt->bindParam(':event_name', $event_name);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':event_location', $event_location);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lng', $lng);
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();

        header("Location: manage_events.php");
        exit();
    }
} catch(PDOException $e) {
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
            <input type="text" class="form-control" id="event_location" name="event_location" value="<?php echo htmlspecialchars($event['event_location']); ?>" oninput="getCoordinates()" required>
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
            <label for="event_image">Image de l'événement</label>
            <input type="file" class="form-control-file" id="event_image" name="event_image">
            <?php if (!empty($event['event_image'])): ?>
                <p>Image actuelle : <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="Image actuelle" width="150"></p>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="manage_events.php" class="btn btn-secondary">Retour</a>
    </form>
</div>

<script>
function getCoordinates() {
    var address = document.getElementById('event_location').value;
    var apiKey = 'YOUR_OPENCAGE_API_KEY';  // Remplacez par votre clé API OpenCage

    fetch(`https://api.opencagedata.com/geocode/v1/json?q=${encodeURIComponent(address)}&key=${apiKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.results.length > 0) {
                var lat = data.results[0].geometry.lat;
                var lng = data.results[0].geometry.lng;

                document.getElementById('lat').value = lat;
                document.getElementById('lng').value = lng;
            }
        })
        .catch(error => console.error('Erreur:', error));
}
</script>

</body>
</html>


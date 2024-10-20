<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'événement</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 300px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
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
            <input type="text" class="form-control" id="event_location" name="event_location" value="<?php echo htmlspecialchars($event['event_location']); ?>" required>
            <input type="hidden" id="lat" name="lat" value="<?php echo htmlspecialchars($event['lat']); ?>">
            <input type="hidden" id="lng" name="lng" value="<?php echo htmlspecialchars($event['lng']); ?>">
        </div>
        <div class="form-group">
            <label for="event_image">Image de l'événement (laisser vide si inchangée)</label>
            <input type="file" class="form-control-file" id="event_image" name="event_image">
        </div>
        <div class="form-group">
            <label for="event_description">Description de l'événement</label>
            <textarea class="form-control" id="event_description" name="event_description"><?php echo htmlspecialchars($event['event_description']); ?></textarea>
        </div>
        <div id="map"></div>
        <button type="submit" class="btn btn-primary mt-3">Modifier</button>
        <a href="manage_events.php" class="btn btn-secondary mt-3">Retour</a>
    </form>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
    var map = L.map('map').setView([<?php echo htmlspecialchars($event['lat']); ?>, <?php echo htmlspecialchars($event['lng']); ?>], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    var marker = L.marker([<?php echo htmlspecialchars($event['lat']); ?>, <?php echo htmlspecialchars($event['lng']); ?>]).addTo(map);

    var geocoder = L.Control.Geocoder.nominatim();
    var control = L.Control.geocoder({
        geocoder: geocoder,
        defaultMarkGeocode: false
    }).on('markgeocode', function(e) {
        var latlng = e.geocode.center;
        marker.setLatLng(latlng).update();
        map.setView(latlng, 13);
        document.getElementById('lat').value = latlng.lat;
        document.getElementById('lng').value = latlng.lng;
        document.getElementById('event_location').value = e.geocode.name;
    }).addTo(map);

    var searchBox = document.getElementById('event_location');
    searchBox.addEventListener('input', function() {
        geocoder.geocode(searchBox.value, function(results) {
            if (results.length > 0) {
                var latlng = results[0].center;
                marker.setLatLng(latlng).update();
                map.setView(latlng, 13);
                document.getElementById('lat').value = latlng.lat;
                document.getElementById('lng').value = latlng.lng;
            }
        });
    });
</script>
</body>
</html>

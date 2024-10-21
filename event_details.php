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
    $stmt = $conn->prepare("SELECT id, event_name, event_date, event_location, event_image FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
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

<!-- Modal d'inscription -->
<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Inscription à l'événement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire d'inscription sera injecté ici -->
                <form id="registrationForm">
                    <!-- Champs supplémentaires à ajouter ici via AJAX -->
                    <div id="extraFields"></div>
                    <button type="button" class="btn btn-primary" id="submitRegistration">S'inscrire</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS et jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function(){
        $('.event-card').on('click', function(){
            var eventId = $(this).data('id');
            
            // Faire une requête AJAX pour obtenir les détails de l'événement
            $.ajax({
                url: 'event_details_ajax.php',  // Page qui renverra les détails de l'événement
                method: 'GET',
                data: { id: eventId },
                success: function(response) {
                    $('#eventDetails').html(response); // Insérer les détails dans le modal
                }
            });
        });

        // Gestion du bouton d'inscription
        $('#submitRegistration').on('click', function() {
            var eventId = $('.event-card').data('id');
            var formData = $('#registrationForm').serialize(); // Sérialise les données du formulaire

            // Requête AJAX pour soumettre l'inscription
            $.ajax({
                url: 'register_event.php',
                method: 'POST',
                data: formData + '&event_id=' + eventId,
                success: function(response) {
                    alert(response); // Afficher le message de confirmation
                    $('#registerModal').modal('hide'); // Fermer la modale
                }
            });
        });

        // Lorsque l'utilisateur clique sur "S'inscrire", afficher le formulaire d'inscription avec les champs supplémentaires
        $(document).on('click', '#registerButton', function() {
            var eventId = $(this).data('id');
            $('#registerModal').modal('show');

            // Charger les champs supplémentaires pour l'événement
            $.ajax({
                url: 'load_event_fields.php',
                method: 'GET',
                data: { event_id: eventId },
                success: function(response) {
                    $('#extraFields').html(response); // Insérer les champs supplémentaires dans la modale
                }
            });
        });
    });
</script>

</body>
</html>

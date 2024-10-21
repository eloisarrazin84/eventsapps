<?php
session_start();

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";  
$password = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer l'ID de l'événement
    if (isset($_GET['event_id'])) {
        $eventId = $_GET['event_id'];

        // Récupérer les informations sur l'événement
        $stmt = $conn->prepare("SELECT event_name FROM events WHERE id = :event_id");
        $stmt->bindParam(':event_id', $eventId);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        // Récupérer les utilisateurs inscrits à cet événement avec les données les plus récentes
        $stmt = $conn->prepare("
    SELECT u.username, ued.field_name, ued.field_value
    FROM users u
    JOIN event_user_assignments eua ON u.id = eua.user_id
    JOIN user_event_data ued ON u.id = ued.user_id
    WHERE eua.event_id = :event_id AND ued.event_id = :event_id
    AND ued.timestamp = (
        SELECT MAX(ued2.timestamp)
        FROM user_event_data ued2
        WHERE ued2.user_id = ued.user_id 
        AND ued2.field_name = ued.field_name 
        AND ued2.event_id = ued.event_id
    )
    ORDER BY u.username, ued.timestamp DESC
");
        $stmt->bindParam(':event_id', $eventId);
        $stmt->execute();
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Détails des inscriptions pour l'événement</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="mt-5">Inscriptions pour l'événement : <?php echo htmlspecialchars($event['event_name']); ?></h1>

    <?php if (!empty($registrations)): ?>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Réponses</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $registration): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($registration['username']); ?></td>
                        <td><?php echo $registration['user_data']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune inscription pour cet événement.</p>
    <?php endif; ?>

    <a href="manage_events.php" class="btn btn-secondary">Retour</a>
</div>
</body>
</html>

<?php
session_start();

// Vérifier que l'utilisateur est un administrateur
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
    // Connexion à la base de données via PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si un ID d'événement est passé en paramètre
    if (isset($_GET['id'])) {
        $eventId = $_GET['id'];

        // Récupérer les informations de l'événement
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            // Si l'événement n'existe pas, afficher un message d'erreur
            echo "Événement non trouvé.";
            exit();
        }

        // Si le formulaire est soumis, mettre à jour l'événement
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $event_name = $_POST['event_name'];
            $event_date = $_POST['event_date'];
            $event_location = $_POST['event_location'];

            // Mettre à jour les informations de l'événement dans la base de données
            $stmt = $conn->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_location = :event_location WHERE id = :id");
            $stmt->bindParam(':event_name', $event_name);
            $stmt->bindParam(':event_date', $event_date);
            $stmt->bindParam(':event_location', $event_location);
            $stmt->bindParam(':id', $eventId);
            $stmt->execute();

            // Redirection après modification
            header("Location: manage_events.php");
            exit();
        }
    } else {
        // Si aucun ID d'événement n'est passé, afficher un message d'erreur
        echo "Aucun événement sélectionné.";
        exit();
    }
} catch (PDOException $e) {
    // Afficher l'erreur si la connexion ou la requête échoue
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

    <!-- Formulaire de modification de l'événement -->
    <form method="POST" action="">
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
        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="manage_events.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>


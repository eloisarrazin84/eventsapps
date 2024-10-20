<?php
session_start();

// Vérification que l'utilisateur est bien administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirige vers la page de connexion si l'utilisateur n'est pas administrateur
    header("Location: login.php");
    exit();
}

// Connexion à la base de données (les informations doivent être sécurisées)
$servername = "localhost";
$username = "root";  // Remplacez par votre utilisateur de base de données
$password = "Lipton2019!";  // Remplacez par votre mot de passe de base de données
$dbname = "outdoorsec";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_location = $_POST['event_location'];
    $event_image = null;

    // Gestion de l'upload de l'image
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $image_name = basename($_FILES['event_image']['name']);
        $image_path = 'uploads/' . $image_name;
        
        // Déplacer l'image uploadée vers le dossier uploads
        if (move_uploaded_file($_FILES['event_image']['tmp_name'], $image_path)) {
            $event_image = $image_path;  // Chemin de l'image à stocker
        } else {
            echo "Erreur lors du déplacement du fichier.";
        }
    } else {
        // Gérer les erreurs lors du téléchargement de l'image
        if ($_FILES['event_image']['error'] != 0) {
            echo "Erreur de téléchargement : " . $_FILES['event_image']['error'];
        }
    }

    try {
        // Connexion à la base de données avec PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insertion du nouvel événement avec son image dans la base de données
        $stmt = $conn->prepare("INSERT INTO events (event_name, event_date, event_location, event_image) 
                                VALUES (:event_name, :event_date, :event_location, :event_image)");
        $stmt->bindParam(':event_name', $event_name);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':event_location', $event_location);
        $stmt->bindParam(':event_image', $event_image);
        $stmt->execute();

        // Redirection après ajout
        header("Location: manage_events.php");
        exit();

    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
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
</head>
<body>
<div class="container">
    <h1 class="mt-5">Ajouter un événement</h1>

    <!-- Formulaire d'ajout d'événement avec envoi de fichier -->
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
            <label for="event_location">Lieu de l'événement</label>
            <input type="text" class="form-control" id="event_location" name="event_location">
        </div>
        <div class="form-group">
            <label for="event_image">Image de l'événement</label>
            <input type="file" class="form-control-file" id="event_image" name="event_image">
        </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
        <a href="manage_events.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>
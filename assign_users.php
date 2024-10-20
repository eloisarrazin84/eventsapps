<?php
session_start();

// Vérification que l'utilisateur est bien un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirige vers la page de connexion si l'utilisateur n'est pas administrateur
    header("Location: login.php");
    exit();
}

// Informations de connexion à la base de données
$servername = "localhost";
$username = "root";  // Remplacez par votre utilisateur de base de données
$password = "Lipton2019!";  // Remplacez par votre mot de passe de base de données
$dbname = "outdoorsec";

try {
    // Connexion à la base de données avec PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si un ID d'événement est passé en paramètre
    if (isset($_GET['event_id'])) {
        $eventId = $_GET['event_id'];

        // Récupérer les utilisateurs avec le rôle "user"
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'user'");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les utilisateurs déjà assignés à cet événement
        $stmt = $conn->prepare("SELECT user_id FROM event_user_assignments WHERE event_id = :event_id");
        $stmt->bindParam(':event_id', $eventId);
        $stmt->execute();
        $assignedUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Traitement du formulaire lors de la soumission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selectedUsers = $_POST['users'] ?? [];

            // Ajouter les nouvelles assignations
            foreach ($selectedUsers as $userId) {
                if (!in_array($userId, $assignedUsers)) {
                    $stmt = $conn->prepare("INSERT INTO event_user_assignments (event_id, user_id) VALUES (:event_id, :user_id)");
                    $stmt->bindParam(':event_id', $eventId);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->execute();
                }
            }

            // Supprimer les utilisateurs qui ne sont plus sélectionnés
            foreach ($assignedUsers as $userId) {
                if (!in_array($userId, $selectedUsers)) {
                    $stmt = $conn->prepare("DELETE FROM event_user_assignments WHERE event_id = :event_id AND user_id = :user_id");
                    $stmt->bindParam(':event_id', $eventId);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->execute();
                }
            }

            // Redirection après l'assignation
            header("Location: manage_events.php");
            exit();
        }

    } else {
        echo "Aucun événement sélectionné.";
        exit();
    }
} catch(PDOException $e) {
    // Gestion des erreurs de base de données
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigner des utilisateurs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="mt-5">Assigner des utilisateurs à l'événement</h1>

    <!-- Formulaire de sélection d'utilisateurs -->
    <form method="POST" action="">
        <div class="form-group">
            <label for="users">Sélectionner les utilisateurs</label>
            <select class="form-control" id="users" name="users[]" multiple>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo in_array($user['id'], $assignedUsers) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Assigner</button>
        <a href="manage_events.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>

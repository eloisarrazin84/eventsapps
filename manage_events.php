<?php
session_start();

// Vérifier si l'utilisateur est un administrateur
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

    // Supprimer un événement via méthode POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
        $eventId = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM events WHERE id = :id");
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();
        header("Location: manage_events.php");
        exit();
    }

    // Récupérer la liste des événements
    $stmt = $conn->prepare("SELECT * FROM events ORDER BY event_date ASC");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des événements</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        .event-card {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .event-image {
            width: 150px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .event-details {
            flex: 1;
            padding-left: 20px;
        }
        .event-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-custom {
            padding: 10px;
            font-size: 0.9rem;
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
<body>

<?php include 'menu.php'; ?>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Gestion des événements</h1>

    <a href="add_event.php" class="btn btn-primary mb-4">Ajouter un événement</a>

    <div class="event-list">
        <?php foreach ($events as $event): ?>
        <div class="event-card">
            <div>
                <?php if (!empty($event['event_image'])): ?>
                    <img class="event-image" src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
                <?php endif; ?>
            </div>
            <div class="event-details">
                <h5><?php echo htmlspecialchars($event['event_name']); ?></h5>
                <p><strong>Date :</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                <p><strong>Lieu :</strong> <?php echo htmlspecialchars($event['event_location']); ?></p>
                <p>
                    <strong>Utilisateurs assignés :</strong> 
                    <?php
                    $stmt = $conn->prepare("SELECT users.username FROM users
                                            JOIN event_user_assignments ON users.id = event_user_assignments.user_id
                                            WHERE event_user_assignments.event_id = :event_id");
                    $stmt->bindParam(':event_id', $event['id']);
                    $stmt->execute();
                    $assignedUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($assignedUsers)) {
                        echo implode(', ', $assignedUsers);
                    } else {
                        echo "Aucun utilisateur assigné";
                    }
                    ?>
                </p>
            </div>
            <div class="event-buttons">
                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning btn-sm btn-custom">Modifier</a>
                <a href="assign_users.php?event_id=<?php echo $event['id']; ?>" class="btn btn-info btn-sm btn-custom">Assigner des utilisateurs</a>
                <!-- Suppression via formulaire POST -->
                <form method="POST" action="manage_events.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?');">
                    <input type="hidden" name="delete_id" value="<?php echo $event['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm btn-custom">Supprimer</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

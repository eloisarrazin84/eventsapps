<?php
session_start();
$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['notifications'] as $notificationType => $isEnabled) {
        // Mettez à jour la préférence de notification dans la base de données
        $stmt = $conn->prepare("INSERT INTO user_notifications (user_id, notification_type, is_enabled) VALUES (:user_id, :notification_type, :is_enabled)
                                 ON DUPLICATE KEY UPDATE is_enabled = :is_enabled");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':notification_type', $notificationType);
        $stmt->bindParam(':is_enabled', $isEnabled);
        $stmt->execute();
    }
}

// Récupérer les notifications de l'utilisateur
$stmt = $conn->prepare("SELECT notification_type, is_enabled FROM user_notifications WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$userNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer un tableau pour stocker les préférences
$notifications = [
    'expire_soon' => 'Médicaments expirant bientôt',
    // Ajoutez d'autres types de notifications ici si nécessaire
];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres de Notification</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h1>Paramètres de Notification</h1>
    <form method="POST" action="">
        <table class="table">
            <thead>
                <tr>
                    <th>Notification</th>
                    <th>Activer/Désactiver</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $type => $description): ?>
                    <?php
                    // Vérifiez si la notification est activée pour l'utilisateur
                    $isEnabled = false;
                    foreach ($userNotifications as $userNotification) {
                        if ($userNotification['notification_type'] === $type) {
                            $isEnabled = $userNotification['is_enabled'];
                            break;
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($description); ?></td>
                        <td>
                            <input type="checkbox" name="notifications[<?php echo $type; ?>]" value="1" <?php echo $isEnabled ? 'checked' : ''; ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Sauvegarder</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$user_id = $_SESSION['user_id'];

// Récupérer tous les types de notification
$notificationTypes = ['expire_soon']; // Ajouter d'autres types si nécessaire
$notifications = [];

// Récupérer les paramètres de notification pour l'utilisateur
foreach ($notificationTypes as $type) {
    $stmt = $conn->prepare("SELECT is_enabled FROM user_notifications WHERE user_id = :user_id AND notification_type = :type");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':type', $type);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $notifications[$type] = $result ? $result['is_enabled'] : 1; // Activer par défaut si pas trouvé
}

// Sauvegarder les paramètres si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($notificationTypes as $type) {
        $is_enabled = isset($_POST[$type]) ? 1 : 0;
        // Vérifier si l'enregistrement existe
        $stmt = $conn->prepare("INSERT INTO user_notifications (user_id, notification_type, is_enabled) VALUES (:user_id, :type, :is_enabled)
                                 ON DUPLICATE KEY UPDATE is_enabled = :is_enabled");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':is_enabled', $is_enabled);
        $stmt->execute();
    }
    echo "Paramètres de notification mis à jour.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres de Notification</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Paramètres de Notification</h2>
    <form method="POST">
        <?php foreach ($notificationTypes as $type): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="<?php echo $type; ?>" name="<?php echo $type; ?>" <?php echo $notifications[$type] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="<?php echo $type; ?>">
                    Notifications pour les médicaments expirant bientôt
                </label>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary mt-3">Sauvegarder</button>
    </form>
</div>
</body>
</html>

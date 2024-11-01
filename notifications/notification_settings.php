<?php
// Démarrer la session et récupérer les informations utilisateur
session_start();
$user_id = $_SESSION['user_id'];

// Connexion unique à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer les notifications actuelles
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire si soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($notifications as $notification) {
        $notificationId = $notification['id'];
        $isEnabled = isset($_POST["notification_$notificationId"]) ? 1 : 0; // 1 pour activé, 0 pour désactivé
        
        // Mettre à jour l'état de la notification
        $updateStmt = $conn->prepare("UPDATE notifications SET is_enabled = :is_enabled WHERE id = :id AND user_id = :user_id");
        $updateStmt->bindParam(':is_enabled', $isEnabled);
        $updateStmt->bindParam(':id', $notificationId);
        $updateStmt->bindParam(':user_id', $user_id);
        $updateStmt->execute();
    }
    echo "<div class='alert alert-success'>Les paramètres de notification ont été mis à jour avec succès.</div>";
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
    <form method="POST" action="">
        <table class="table">
            <thead>
                <tr>
                    <th>Notification</th>
                    <th>Activer/Désactiver</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $notification): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($notification['message']); ?></td>
                        <td>
                            <input type="checkbox" name="notification_<?php echo $notification['id']; ?>" value="1" <?php echo $notification['is_enabled'] ? 'checked' : ''; ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Sauvegarder</button>
    </form>
</div>
</body>
</html>

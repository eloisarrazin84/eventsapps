<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier si l'utilisateur est connecté et a le bon rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer les notifications actuelles
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire si soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $notificationId = $_POST['notification_id'];
    $isEnabled = isset($_POST['is_enabled']) ? 1 : 0; // 1 pour activé, 0 pour désactivé
    
    // Mettre à jour l'état de la notification
    $updateStmt = $conn->prepare("UPDATE notifications SET is_enabled = :is_enabled WHERE id = :id");
    $updateStmt->bindParam(':is_enabled', $isEnabled);
    $updateStmt->bindParam(':id', $notificationId);
    $updateStmt->execute();
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
                            <input type="checkbox" name="is_enabled" value="1" <?php echo $notification['is_enabled'] ? 'checked' : ''; ?>>
                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
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

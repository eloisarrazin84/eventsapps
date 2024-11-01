<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer tous les utilisateurs
$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les types de notifications
$stmt = $conn->prepare("SELECT * FROM notification_types");
$stmt->execute();
$notificationTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mise à jour des paramètres de notification
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($users as $user) {
        $userId = $user['id'];
        $settings = [];
        foreach ($notificationTypes as $type) {
            $settings[$type['id']] = isset($_POST["user_{$userId}_type_{$type['id']}"]) ? 1 : 0;
        }
        $stmt = $conn->prepare("UPDATE users SET notification_settings = :settings WHERE id = :user_id");
        $stmt->bindParam(':settings', json_encode($settings));
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
    }
    echo "<script>alert('Les paramètres de notification ont été mis à jour.');</script>";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Notifications</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            max-width: 800px;
            margin-top: 50px;
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-weight: bold;
            color: #007bff;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Paramètres de Notification</h2>
    <form method="POST">
        <table class="table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <?php foreach ($notificationTypes as $type): ?>
                        <th><?php echo htmlspecialchars($type['name']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <?php foreach ($notificationTypes as $type): ?>
                            <?php
                            // Récupérer les paramètres de notification pour cet utilisateur
                            $settings = json_decode($user['notification_settings'], true);
                            $isChecked = (isset($settings[$type['id']]) && $settings[$type['id']] == 1) ? 'checked' : '';
                            ?>
                            <td>
                                <input type="checkbox" name="user_<?php echo $user['id']; ?>_type_<?php echo $type['id']; ?>" <?php echo $isChecked; ?>>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

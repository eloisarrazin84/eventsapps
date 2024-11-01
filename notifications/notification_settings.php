<?php
session_start();
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sauvegarder les paramètres de notification dans la base de données
    $user_id = $_POST['user_id'];
    $notification_type = $_POST['notification_type'];
    
    $stmt = $conn->prepare("UPDATE users SET notification_type = :notification_type WHERE id = :user_id");
    $stmt->bindParam(':notification_type', $notification_type);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
}

// Récupérer les utilisateurs pour afficher les options de configuration
$stmt = $conn->prepare("SELECT id, username, notification_type FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres de Notification</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h1>Paramètres de Notification</h1>
    <form method="POST" class="mb-4">
        <div class="form-group">
            <label for="user_id">Sélectionner un utilisateur:</label>
            <select name="user_id" id="user_id" class="form-control" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="notification_type">Type de notification:</label>
            <select name="notification_type" id="notification_type" class="form-control">
                <option value="none">Aucune</option>
                <option value="expire_soon">Médicaments expirant bientôt</option>
                <!-- Ajoutez d'autres types de notifications ici -->
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Sauvegarder</button>
    </form>
</div>
</body>
</html>

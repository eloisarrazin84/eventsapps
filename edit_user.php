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

    // Vérifier si l'ID de l'utilisateur est passé en paramètre dans l'URL
    if (isset($_GET['id'])) {
        $userId = $_GET['id'];

        // Récupérer les informations de l'utilisateur
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "Utilisateur non trouvé.";
            exit();
        }

        // Traitement du formulaire lors de la soumission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $role = $_POST['role'];

            // Vérifier si un nouveau mot de passe est défini
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = :username, password = :password, role = :role WHERE id = :id");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password);
            } else {
                // Si pas de nouveau mot de passe, on met à jour sans changer le mot de passe
                $stmt = $conn->prepare("UPDATE users SET username = :username, role = :role WHERE id = :id");
                $stmt->bindParam(':username', $username);
            }
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();

            header("Location: manage_users.php");
            exit();
        }
    } else {
        echo "Aucun utilisateur sélectionné.";
        exit();
    }
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier utilisateur</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="mt-5">Modifier l'utilisateur</h1>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Nouveau mot de passe (laisser vide si inchangé)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="role">Rôle</label>
            <select class="form-control" id="role" name="role">
                <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>Utilisateur standard</option>
                <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Administrateur</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="manage_users.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>

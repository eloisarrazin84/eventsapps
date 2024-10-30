<?php
session_start();

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
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

        // Récupérer les applications assignées à l'utilisateur
        $stmt = $conn->prepare("SELECT application_name FROM user_applications WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $assignedApplications = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Traitement du formulaire lors de la soumission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $firstName = $_POST['first_name'];
            $lastName = $_POST['last_name'];
            $role = $_POST['role'];
            $applications = isset($_POST['applications']) ? $_POST['applications'] : [];

            // Vérifier si un nouveau mot de passe est défini
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email, first_name = :first_name, last_name = :last_name, password = :password, role = :role WHERE id = :id");
                $stmt->bindParam(':password', $password);
            } else {
                // Mise à jour sans changer le mot de passe
                $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email, first_name = :first_name, last_name = :last_name, role = :role WHERE id = :id");
            }
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();

            // Réinitialiser les applications de l'utilisateur
            $stmt = $conn->prepare("DELETE FROM user_applications WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            // Assigner de nouvelles applications
            foreach ($applications as $app) {
                $stmt = $conn->prepare("INSERT INTO user_applications (user_id, application_name) VALUES (:user_id, :application_name)");
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':application_name', $app);
                $stmt->execute();
            }

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
    <title>Modifier l'utilisateur</title>
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
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Nom</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
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

        <div class="form-group">
            <label>Applications assignées</label><br>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="gestion_pharmacie" name="applications[]" value="gestion_pharmacie" <?php echo in_array('gestion_pharmacie', $assignedApplications) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="gestion_pharmacie">Gestion Pharmacie</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="notes_de_frais" name="applications[]" value="notes_de_frais" <?php echo in_array('notes_de_frais', $assignedApplications) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="notes_de_frais">Notes de Frais</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="autre_application" name="applications[]" value="autre_application" <?php echo in_array('autre_application', $assignedApplications) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="autre_application">Autre Application</label>
            </div>
            <small class="form-text text-muted">Sélectionnez les applications auxquelles l'utilisateur aura accès.</small>
        </div>

        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="manage_users.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>

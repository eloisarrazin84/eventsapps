<?php
session_start();
$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$servername = "localhost";
$username_db = "root";  
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations utilisateur
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Gestion de la mise à jour du profil
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $updateStmt = $conn->prepare("UPDATE users SET username = :username, email = :email, password = :password WHERE id = :user_id");
                $updateStmt->bindParam(':password', $hashed_password);
            } else {
                echo "<script>alert('Les mots de passe ne correspondent pas.');</script>";
                exit();
            }
        } else {
            $updateStmt = $conn->prepare("UPDATE users SET username = :username, email = :email WHERE id = :user_id");
        }

        $updateStmt->bindParam(':username', $username);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->bindParam(':user_id', $user_id);
        $updateStmt->execute();

        echo "<script>alert('Profil mis à jour avec succès!');</script>";
    }

    // Récupérer les documents actuels
    $stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Mon profil</title>
   <style>
    .profile-container {
        background-color: #f7f9fc;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .profile-header {
        background-color: #007bff;
        color: white;
        padding: 10px;
        border-radius: 10px 10px 0 0;
        text-align: center;
    }

    .form-control, .btn {
        border-radius: 50px;
        padding: 12px;
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-secondary {
        background-color: #6c757d;
    }

    .card {
        margin-top: 20px;
        padding: 20px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .document-upload {
        padding: 20px;
        background-color: #e9f1ff;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .table td, .table th {
        vertical-align: middle;
    }
</style>

<!-- Page de profil -->
<div class="container mt-5">
    <div class="profile-container">
        <div class="profile-header">
            <h1>Votre profil</h1>
        </div>

        <!-- Formulaire pour modifier le profil -->
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
                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="new_password" name="new_password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Modifier</button>
        </form>
        <a href="dashboard.php" class="btn btn-secondary btn-block mt-3">Retour au tableau de bord</a>
    </div>

    <!-- Section Documents -->
    <div class="card">
        <h3 class="text-center">Vos Documents</h3>
        <div class="document-upload">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="document">Télécharger un document</label>
                    <input type="file" class="form-control" id="document" name="document">
                </div>
                <button type="submit" class="btn btn-success btn-block">Télécharger</button>
            </form>
        </div>

        <!-- Liste des documents -->
        <h4>Documents Actuels</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nom du Document</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doc['file_name']); ?></td>
                        <td>
                            <a href="view_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-info btn-sm">Voir</a>
                            <a href="delete_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Script pour afficher la notification -->
<script>
    $(document).ready(function() {
        <?php if (isset($_POST['username'])): ?>
            alert('Profil mis à jour avec succès!');
        <?php endif; ?>
    });
</script>

</body>
</html>

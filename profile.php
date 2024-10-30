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
        $first_name = htmlspecialchars($_POST['first_name']);
        $last_name = htmlspecialchars($_POST['last_name']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validation des mots de passe
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $updateStmt = $conn->prepare("UPDATE users SET username = :username, email = :email, first_name = :first_name, last_name = :last_name, password = :password WHERE id = :user_id");
                $updateStmt->bindParam(':password', $hashed_password);
            } else {
                echo "<script>alert('Les mots de passe ne correspondent pas.');</script>";
                exit();
            }
        } else {
            // Mise à jour sans changer le mot de passe
            $updateStmt = $conn->prepare("UPDATE users SET username = :username, email = :email, first_name = :first_name, last_name = :last_name WHERE id = :user_id");
        }

        $updateStmt->bindParam(':username', $username);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->bindParam(':first_name', $first_name);
        $updateStmt->bindParam(':last_name', $last_name);
        $updateStmt->bindParam(':user_id', $user_id);
        $updateStmt->execute();

        echo "<script>alert('Profil mis à jour avec succès!');</script>";
    }

    // Gestion de l'upload de la photo de profil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/profile_pictures/";

        // Créer le répertoire si non existant
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Mettre à jour la photo dans la base de données
            $stmt = $conn->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id");
            $stmt->bindParam(':profile_picture', $target_file);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        } else {
            echo "<script>alert('Erreur lors du téléchargement de la photo de profil.');</script>";
        }
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
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="profile-container">
        <div class="profile-header">
            <h1>Votre profil</h1>
        </div>
        <!-- Formulaire pour photo de profil -->
        <form method="POST" enctype="multipart/form-data" action="">
            <div class="form-group">
                <label for="profile_picture">Photo de profil</label>
                <input type="file" class="form-control-file" id="profile_picture" name="profile_picture">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil" class="img-thumbnail mt-2" style="max-width: 150px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Modifier la photo</button>
        </form>

        <!-- Formulaire pour modifier le profil -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="first_name">Prénom</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Nom</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>
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
    </div>

    <!-- Section Documents -->
    <div class="card mt-4">
        <h3 class="text-center">Vos Documents</h3>
        <div class="document-upload">
            <form method="POST" enctype="multipart/form-data" action="upload_document.php">
                <div class="form-group">
                    <label for="documents">Télécharger des documents</label>
                    <input type="file" class="form-control" id="documents" name="documents[]" multiple>
                </div>
                <div id="document-names"></div> <!-- Champ pour renommer les documents -->
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
                        <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                        <td>
                            <a href="view_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-info btn-sm">Voir</a>
                            <a href="delete_document.php?id=<?php echo $doc['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bouton de retour au tableau de bord en fin de page -->
    <div class="mt-5 text-center">
        <a href="home.php" class="btn btn-secondary">Retour aux applications</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Ajouter des champs de texte pour renommer les documents
    document.getElementById('documents').addEventListener('change', function () {
        const fileList = this.files;
        const container = document.getElementById('document-names');
        container.innerHTML = '';  // Vider les anciens champs

        for (let i = 0; i < fileList.length; i++) {
            const file = fileList[i];
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control mt-2';
            input.name = 'document_names[]';  // Nom du champ pour récupérer les noms
            input.placeholder = `Nom pour ${file.name}`;
            container.appendChild(input);
        }
    });
</script>

</body>
</html>

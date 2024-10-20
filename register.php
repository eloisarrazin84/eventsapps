<?php
session_start();
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $username = htmlspecialchars($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);

    if (!$email) {
        $error = "Adresse email invalide.";
    } else {
        // Connexion à la base de données
        $servername = "localhost";
        $username_db = "root";
        $password_db = "Lipton2019!";
        $dbname = "outdoorsec";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Insertion de l'utilisateur en attente de validation
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, first_name, last_name, is_approved) 
                                    VALUES (:username, :password, :email, :first_name, :last_name, FALSE)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->execute();

            // Récupérer l'ID de l'utilisateur nouvellement créé
            $userId = $conn->lastInsertId();

            // Gérer les fichiers joints (diplômes, cartes professionnelles, etc.)
            foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['documents']['name'][$key];
                $file_tmp = $_FILES['documents']['tmp_name'][$key];
                $file_type = mime_content_type($file_tmp);
                $file_size = $_FILES['documents']['size'][$key];
                $file_path = "uploads/" . basename($file_name);

                // Vérifier le type et la taille du fichier
                if ($file_size > 5000000 || !in_array($file_type, ['application/pdf', 'image/jpeg', 'image/png'])) {
                    $error = "Fichier invalide ou trop volumineux.";
                } else {
                    // Téléverser le fichier dans le dossier uploads
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        // Insérer les informations du document dans la base de données
                        $stmt = $conn->prepare("INSERT INTO documents (user_id, file_name, file_path) VALUES (:user_id, :file_name, :file_path)");
                        $stmt->bindParam(':user_id', $userId);
                        $stmt->bindParam(':file_name', $file_name);
                        $stmt->bindParam(':file_path', $file_path);
                        $stmt->execute();
                    }
                }
            }

            $success = "Votre compte a été créé. Il doit être validé par un administrateur.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Inscription</title>
</head>
<body>
<div class="container">
    <h2 class="mt-5">Inscription</h2>

    <!-- Message de succès ou d'erreur -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Nom</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>
        <div class="form-group">
            <label for="documents">Joindre des documents (diplômes, cartes professionnelles, etc.)</label>
            <input type="file" class="form-control-file" id="documents" name="documents[]" multiple>
        </div>
        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

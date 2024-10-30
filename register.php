<?php
session_start();
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $address = htmlspecialchars($_POST['address']);
    $phone = htmlspecialchars($_POST['phone']);

    // Vérifier si les mots de passe correspondent
    if ($password !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (!isStrongPassword($password)) {
        $error = "Le mot de passe doit contenir au moins 8 caractères, incluant des lettres, des chiffres et des caractères spéciaux.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Générer le nom d'utilisateur
        $username = strtolower(substr($firstName, 0, 1) . $lastName); // 1ère lettre du prénom + nom de famille

        // Vérification de l'upload de la photo de profil
        $profilePicturePath = '';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $target_dir = "uploads/profile_pictures/";

            // Créer le répertoire si non existant
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
            $file_type = mime_content_type($_FILES["profile_picture"]["tmp_name"]);
            
            // Vérification du type de fichier
            if (in_array($file_type, ['image/jpeg', 'image/png', 'image/gif'])) {
                // Déplacer le fichier téléchargé
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    $profilePicturePath = $target_file; // Chemin de la photo de profil
                } else {
                    $error = "Erreur lors du téléchargement de la photo de profil.";
                }
            } else {
                $error = "Type de fichier non autorisé pour la photo de profil.";
            }
        }

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
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, first_name, last_name, profile_picture, address, phone, is_approved) 
                                        VALUES (:username, :password, :email, :first_name, :last_name, :profile_picture, :address, :phone, FALSE)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':first_name', $firstName);
                $stmt->bindParam(':last_name', $lastName);
                $stmt->bindParam(':profile_picture', $profilePicturePath);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':phone', $phone);
                $stmt->execute();

                $success = "Votre compte a été créé. Il doit être validé par un administrateur.";
            } catch (PDOException $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}

// Fonction pour vérifier la force du mot de passe
function isStrongPassword($password) {
    return preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Inscription</title>
    <style>
        /* Styles personnalisés */
        body {
            background-color: #f7f9fc;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .btn {
            border-radius: 50px;
        }
        .form-control {
            border-radius: 50px;
        }
        .text-danger {
            color: red; /* Couleur pour les champs obligatoires */
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Inscription</h2>

    <!-- Message de succès ou d'erreur -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <!-- Section Photo de Profil -->
        <div class="form-group">
            <label for="profile_picture">Photo de profil <span class="text-danger">*</span></label>
            <input type="file" class="form-control-file" id="profile_picture" name="profile_picture" required>
        </div>

        <!-- Section Identifiants -->
        <fieldset class="border p-3 mb-3">
            <legend class="w-auto">Identifiants</legend>
            <div class="form-group">
                <label for="username">Nom d'utilisateur <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" readonly>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <div class="password-feedback" id="passwordFeedback"></div>
            </div>
        </fieldset>

        <!-- Section Informations Personnelles -->
        <fieldset class="border p-3 mb-3">
            <legend class="w-auto">Informations Personnelles</legend>
            <div class="form-group">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="first_name">Prénom <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="first_name" name="first_name" oninput="updateUsername()" required>
            </div>
            <div class="form-group">
                <label for="last_name">Nom <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="last_name" name="last_name" oninput="updateUsername()" required>
            </div>
            <div class="form-group">
                <label for="address">Adresse <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="phone">Numéro de téléphone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
            </div>
        </fieldset>

        <button type="submit" class="btn btn-primary" id="submitBtn">S'inscrire</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    // Mise à jour automatique du nom d'utilisateur
    function updateUsername() {
        const firstName = document.getElementById('first_name').value;
        const lastName = document.getElementById('last_name').value;
        const usernameField = document.getElementById('username');

        if (firstName && lastName) {
            const username = firstName.charAt(0).toLowerCase() + lastName.toLowerCase();
            usernameField.value = username;
        }
    }

    // Vérification des mots de passe
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const feedback = document.getElementById('passwordFeedback');
    const submitBtn = document.getElementById('submitBtn');

    confirmPasswordField.addEventListener('input', function () {
        if (confirmPasswordField.value !== passwordField.value) {
            feedback.textContent = "Les mots de passe ne correspondent pas.";
            feedback.style.color = 'red';
            submitBtn.disabled = true; // Désactiver le bouton si les mots de passe ne correspondent pas
        } else {
            feedback.textContent = ""; // Clear feedback if passwords match
            submitBtn.disabled = false; // Activer le bouton si les mots de passe correspondent
        }
    });
</script>
</body>
</html>

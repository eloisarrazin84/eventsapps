<?php
session_start();
require_once 'sendEmail.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $address = htmlspecialchars($_POST['address']);
    $phone = htmlspecialchars($_POST['phone']);

    if ($password !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (!isStrongPassword($password)) {
        $error = "Le mot de passe doit contenir au moins 8 caractères, incluant des lettres, des chiffres et des caractères spéciaux.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $username = strtolower(substr($firstName, 0, 1) . $lastName);

        $profilePicturePath = '';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $target_dir = "uploads/profile_pictures/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
            $file_type = mime_content_type($_FILES["profile_picture"]["tmp_name"]);
            if (in_array($file_type, ['image/jpeg', 'image/png', 'image/gif'])) {
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    $profilePicturePath = $target_file;
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
            $servername = "localhost";
            $username_db = "root";
            $password_db = "Lipton2019!";
            $dbname = "outdoorsec";

            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

                // Charger et personnaliser le template de l'email
                $templatePath = 'email_templates/confirmation_registration.html';
                $emailBody = file_get_contents($templatePath);
                $emailBody = str_replace('{{first_name}}', $firstName, $emailBody);

                if (sendEmail($email, "Confirmation d'inscription", $emailBody)) {
                    $success = "Votre compte a été créé. Un email de confirmation vous a été envoyé.";
                } else {
                    $success = "Votre compte a été créé, mais l'email de confirmation n'a pas pu être envoyé.";
                }

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
    <title>Inscription Moderne</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }
        .form-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }
        .section-title {
            font-size: 1.2rem;
            color: #007bff;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .form-group label {
            font-weight: bold;
        }
        .asterisk {
            color: #d9534f;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 25px;
            transition: 0.3s;
            cursor: not-allowed;
        }
        .btn-primary.active {
            cursor: pointer;
            opacity: 1;
        }
        .form-feedback {
            color: #d9534f;
            font-size: 0.9rem;
        }
        .password-strength {
            height: 6px;
            border-radius: 4px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="form-container">
        <h2 class="text-center mb-4">Inscription</h2>
        
        <!-- Afficher les messages d'erreur ou de succès -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Formulaire d'inscription -->
        <form method="POST" action="" enctype="multipart/form-data" id="registrationForm">
            <div class="section-title">Informations personnelles</div>
            <div class="form-group">
                <label for="first_name">Prénom <span class="asterisk">*</span></label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Nom <span class="asterisk">*</span></label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" readonly>
            </div>
            <div class="form-group">
                <label for="address">Adresse <span class="asterisk">*</span></label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="email">Email <span class="asterisk">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required>
                <small class="form-feedback" id="emailFeedback"></small>
            </div>
            
            <div class="section-title">Informations de connexion</div>
            <div class="form-group">
                <label for="password">Mot de passe <span class="asterisk">*</span></label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="password-strength bg-danger mt-2" id="passwordStrength"></div>
                <small class="form-feedback" id="passwordFeedback"></small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe <span class="asterisk">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <small class="form-feedback" id="confirmPasswordFeedback"></small>
            </div>
            
            <div class="section-title">Téléchargement de photo</div>
            <div class="form-group">
                <label for="profile_picture">Photo de profil</label>
                <input type="file" class="form-control-file" id="profile_picture" name="profile_picture">
            </div>
            
            <button type="submit" class="btn btn-primary mt-4" id="submitButton" disabled>S'inscrire</button>
        </form>
    </div>
</div>

<!-- Scripts JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {
        let requiredFields = ['first_name', 'last_name', 'email', 'password', 'confirm_password'];

        $('#first_name, #last_name').on('input', function() {
            let firstName = $('#first_name').val();
            let lastName = $('#last_name').val();
            $('#username').val(firstName.charAt(0).toLowerCase() + lastName.toLowerCase());
            checkFormCompletion();
        });

        $('#password').on('input', function() {
            let strength = checkPasswordStrength($(this).val());
            $('#passwordStrength').removeClass().addClass('password-strength ' + ['bg-danger', 'bg-warning', 'bg-info', 'bg-success'][strength.level]);
            $('#passwordFeedback').text(strength.feedback);
        });

        $('#confirm_password').on('input', function() {
            let match = $(this).val() === $('#password').val();
            $('#confirmPasswordFeedback').text(match ? '' : 'Les mots de passe ne correspondent pas');
            checkFormCompletion();
        });

        function checkFormCompletion() {
            let allFilled = requiredFields.every(field => $('#' + field).val().trim() !== '');
            let passwordsMatch = $('#password').val() === $('#confirm_password').val();
            $('#submitButton').prop('disabled', !(allFilled && passwordsMatch)).toggleClass('active', allFilled && passwordsMatch);
        }
    });

    function checkPasswordStrength(password) {
        let strength = { level: 0, feedback: '' };
        if (password.length < 8) {
            strength.feedback = 'Minimum 8 caractères';
        } else {
            if (/[A-Z]/.test(password)) strength.level++;
            if (/[a-z]/.test(password)) strength.level++;
            if (/[0-9]/.test(password)) strength.level++;
            if (/[^A-Za-z0-9]/.test(password)) strength.level++;
            if (strength.level < 2) strength.feedback = 'Ajoutez des lettres majuscules et chiffres';
            else if (strength.level < 4) strength.feedback = 'Mot de passe fort';
        }
        return strength;
    }
</script>
</body>
</html>

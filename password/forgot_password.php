<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '/../mail/EmailService.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);

    // Connexion à la base de données
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifiez si l'email existe dans la base de données
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $message = "";
        $messageClass = "";

        if ($user) {
            // Créez un token de réinitialisation et l'enregistrez dans la base de données
            $token = bin2hex(random_bytes(50));
            $stmt = $conn->prepare("UPDATE users SET reset_token = :token, token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Envoyez un email avec le lien de réinitialisation
            $emailService = new EmailService();
            $resetLink = "https://event.outdoorsecours.fr/password/reset_password.php?token=" . urlencode($token);
            $emailBody = "<p>Bonjour,</p><p>Veuillez cliquer sur le lien suivant pour réinitialiser votre mot de passe : <a href='$resetLink'>$resetLink</a></p>";
            $emailService->sendEmail($email, "Réinitialisation de votre mot de passe", $emailBody);

            $message = "Un email de réinitialisation a été envoyé si l'adresse email est correcte.";
            $messageClass = "alert alert-success";
        } else {
            $message = "Aucun utilisateur trouvé avec cet email.";
            $messageClass = "alert alert-danger";
        }
    } catch (PDOException $e) {
        $message = "Erreur : " . $e->getMessage();
        $messageClass = "alert alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        h2 {
            color: #007bff;
        }
        .alert {
            font-size: 1.1em;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Réinitialisation du mot de passe</h2>
        <p>Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Réinitialiser le mot de passe</button>
        </form>
    </div>
</body>
</html>

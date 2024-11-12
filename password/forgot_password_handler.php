<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../mail/EmailService.php'; // Chemin absolu pour EmailService.php

// Assurez-vous de configurer les informations de connexion à la base de données ici
$servername = "localhost";
$username_db = "root";
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

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

            echo "Un email de réinitialisation a été envoyé si l'adresse email est correcte.";
        } else {
            echo "Aucun utilisateur trouvé avec cet email.";
        }

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "Méthode de requête non autorisée.";
}

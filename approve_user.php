<?php
session_start();
require_once __DIR__ . '/mail/sendEmail.php';

// Affichage des erreurs PHP pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fonction de chargement du template avec le contenu intégré
function loadTemplate($variables) {
    $templateContent = <<<HTML
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; }
            .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
            h1 { color: #007bff; }
            .button { display: inline-block; padding: 10px 15px; color: white; background-color: #007bff; text-decoration: none; border-radius: 5px; }
            .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Compte approuvé</h1>
            <p>Bonjour {{ first_name }} {{ last_name }},</p>
            <p>Félicitations ! Votre compte a été approuvé. Vous pouvez désormais vous connecter à notre plateforme en utilisant votre identifiant et votre mot de passe.</p>
            <a href="https://event.outdoorsecours.fr/login.php" class="button">Se connecter</a>
            <div class="footer">
                <p>Outdoor Secours - 2024</p>
            </div>
        </div>
    </body>
    </html>
    HTML;

    // Remplacement des variables
    foreach ($variables as $key => $value) {
        $templateContent = str_replace("{{ $key }}", $value, $templateContent);
    }

    return $templateContent;
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
$dbname = "outdoorsec";

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Mettre à jour l'approbation de l'utilisateur
        $stmt = $conn->prepare("UPDATE users SET is_approved = 1 WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        // Récupérer les informations de l'utilisateur approuvé
        $stmt = $conn->prepare("SELECT email, first_name, last_name FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Préparer les variables du template
            $email = $user['email'];
            $subject = "Votre compte a été approuvé !";
            $variables = [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ];

            // Charger le template et envoyer l'email
            $templateContent = loadTemplate($variables);
            try {
                if (!sendEmail($email, $subject, $templateContent)) {
                    error_log("Erreur d'envoi d'email : envoi échoué pour $email");
                    echo "<script>alert('Erreur lors de l\'envoi de l\'email de confirmation.');</script>";
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'envoi de l'email : " . $e->getMessage());
                echo "<script>alert('Erreur : " . $e->getMessage() . "');</script>";
            }
        } else {
            echo "<script>alert('Erreur : Utilisateur introuvable.');</script>";
        }

        // Afficher le pop-up de confirmation et rediriger vers la page de gestion des utilisateurs
        echo "<script>
                alert('Approbation réussie pour l\\'utilisateur ID $userId.');
                window.location.href = 'manage_users.php';
              </script>";

    } catch (PDOException $e) {
        error_log("Erreur de base de données : " . $e->getMessage());
        echo "<script>alert('Erreur de base de données : " . $e->getMessage() . "');</script>";
    }
} else {
    error_log("ID utilisateur manquant dans la requête");
    echo "<script>alert('Erreur : ID utilisateur manquant.');</script>";
}
?>

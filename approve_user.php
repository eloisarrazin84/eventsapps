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

// Fonction de chargement du template
function loadTemplate($templateName, $variables) {
    $templatePath = __DIR__ . "/email_templates/$templateName.html";
    if (file_exists($templatePath)) {
        $templateContent = file_get_contents($templatePath);
        foreach ($variables as $key => $value) {
            $templateContent = str_replace("{{ $key }}", $value, $templateContent);
        }
        return $templateContent;
    } else {
        error_log("Template non trouvé: $templatePath");
        throw new Exception("Template non trouvé: $templatePath");
    }
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
            $templateContent = loadTemplate("user_approved", $variables);
            try {
                if (!sendEmail($email, $subject, $templateContent)) {
                    error_log("Erreur d'envoi d'email : envoi échoué pour $email");
                    echo "Erreur : envoi de l'email de confirmation échoué.";
                } else {
                    echo "Email de confirmation envoyé avec succès.";
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'envoi de l'email : " . $e->getMessage());
                echo "Erreur : " . $e->getMessage();
            }
        } else {
            echo "Erreur : Utilisateur introuvable.";
        }

        // Journalisation avant la redirection
        error_log("Approbation de l'utilisateur réussie : $userId");
        echo "Approbation réussie pour l'utilisateur ID $userId. Vérifiez les logs pour l'état de l'email.";

    } catch (PDOException $e) {
        error_log("Erreur de base de données : " . $e->getMessage());
        echo "Erreur de base de données : " . $e->getMessage();
    }
} else {
    error_log("ID utilisateur manquant dans la requête");
    echo "Erreur : ID utilisateur manquant.";
}

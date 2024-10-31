<?php
session_start();
require_once __DIR__ . '/mail/sendEmail.php'; // Utilisation du chemin correct pour inclure la fonction d'envoi d'email

// Afficher les erreurs PHP pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fonction pour charger le contenu du template d'email
function loadTemplate($templateName, $variables) {
    $templatePath = __DIR__ . "/email_templates/$templateName.html";
    if (file_exists($templatePath)) {
        $templateContent = file_get_contents($templatePath);
        foreach ($variables as $key => $value) {
            $templateContent = str_replace("{{ $key }}", $value, $templateContent);
        }
        return $templateContent;
    } else {
        throw new Exception("Template not found: $templateName");
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

        // Mettre à jour la colonne is_approved pour approuver l'utilisateur
        $stmt = $conn->prepare("UPDATE users SET is_approved = 1 WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        // Récupérer les informations de l'utilisateur approuvé pour l'email
        $stmt = $conn->prepare("SELECT email, first_name, last_name FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Préparer les variables pour le template d'email
            $email = $user['email'];
            $subject = "Votre compte a été approuvé !";
            $variables = [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ];

            // Charger le contenu du template et envoyer l'email
            $templateContent = loadTemplate("user_approved", $variables);
            try {
                if (!sendEmail($email, $subject, $templateContent)) {
                    error_log("Erreur d'envoi d'email.");
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'envoi de l'email : " . $e->getMessage());
            }
        }

        // Rediriger vers la page de gestion des utilisateurs avec un message de succès
        header("Location: manage_users.php");
        exit();
    } catch (PDOException $e) {
        error_log("Erreur de base de données : " . $e->getMessage());
    }
}

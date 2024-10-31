<?php
session_start();
require_once __DIR__ . '/mail/EmailService.php'; // Classe pour envoyer les e-mails
require_once __DIR__ . '/mail/EmailTemplate.php'; // Classe pour charger les templates

// Activer les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
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
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Récupérer les informations de l'utilisateur approuvé
        $stmt = $conn->prepare("SELECT email, first_name, last_name FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Préparer les variables pour le template
            $email = $user['email'];
            $subject = "Votre compte a été approuvé !";
            $variables = [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ];

            // Envoyer l'email avec le service d'email
            $emailService = new EmailService();
            if ($emailService->sendEmail($email, $subject, 'user_approved', $variables)) {
                echo "<script>alert('Email de confirmation envoyé avec succès.');</script>";
            } else {
                error_log("Erreur d'envoi d'email : envoi échoué pour $email");
                echo "<script>alert('Erreur lors de l\'envoi de l\'email de confirmation.');</script>";
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

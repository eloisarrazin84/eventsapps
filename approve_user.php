<?php
session_start();
require_once 'email/sendEmail.php'; // Inclusion de la fonction d'envoi d'email

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

            // Envoyer l'email de confirmation d'approbation
            sendEmail($email, $subject, "user_approved", $variables);
        }

        // Rediriger vers la page de gestion des utilisateurs avec un message de succès
        header("Location: manage_users.php");
        exit();
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

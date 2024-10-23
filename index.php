<?php
session_start();

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: home.php"); // Rediriger vers la page d'accueil si connecté
    exit();
} else {
    header("Location: login.php"); // Rediriger vers la page de connexion si non connecté
    exit();
}
?>

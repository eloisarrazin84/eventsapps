<?php
session_start();

// Vérifiez que l'utilisateur est connecté et qu'il est un administrateur
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    // Si l'utilisateur est déjà un admin, redirigez vers la procédure de retour
    header("Location: return_procedure.php?bag_id=" . $_GET['bag_id']);
    exit();
} else {
    // Redirigez vers la page de connexion avec un retour vers cette page une fois connecté
    header("Location: login.php?redirect=return_procedure.php&bag_id=" . $_GET['bag_id']);
    exit();
}

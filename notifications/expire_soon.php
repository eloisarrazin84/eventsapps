<?php
session_start();
$user_id = $_SESSION['user_id'];

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les médicaments expirant dans moins de 30 jours
$stmt = $conn->prepare("SELECT nom, date_expiration, numero_lot 
                         FROM medicaments 
                         WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$expiringSoonMeds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stocker les médicaments dans la session pour l'affichage dans le menu de notification
if (!empty($expiringSoonMeds)) {
    $_SESSION['expiringSoonMeds'] = $expiringSoonMeds;
} else {
    $_SESSION['expiringSoonMeds'] = [];
}
?>

<?php
require 'EmailService.php'; // Assurez-vous que votre service d'envoi d'e-mails est correctement inclus

function getExpiringMeds($conn) {
    $stmt = $conn->prepare("
        SELECT nom, date_expiration, numero_lot, location_name 
        FROM medicaments m 
        JOIN stock_locations sl ON m.stock_location_id = sl.id 
        WHERE m.date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les médicaments qui expirent
$expiringMeds = getExpiringMeds($conn);

if (!empty($expiringMeds)) {
    $body = "<h1>Récapitulatif des Médicaments Expirants</h1><ul>";
    foreach ($expiringMeds as $med) {
        $body .= "<li>" . htmlspecialchars($med['nom']) . " - Lot: " . htmlspecialchars($med['numero_lot']) . ", Expire le: " . htmlspecialchars($med['date_expiration']) . ", Lieu: " . htmlspecialchars($med['location_name']) . "</li>";
    }
    $body .= "</ul>";

    // Envoyer l'e-mail
    $emailService = new EmailService();
    $emailService->sendEmail('contact@outdoorsecours.fr', 'Récapitulatif des Médicaments Expirants', $body);
} else {
    // Pas de médicaments expirants, vous pouvez décider d'envoyer un e-mail informant qu'il n'y a rien à signaler si nécessaire
    $body = "<h1>Aucun Médicament N'est en Cours d'Expiration</h1>";
    $emailService = new EmailService();
    $emailService->sendEmail('contact@outdoorsecours.fr', 'Récapitulatif des Médicaments Expirants', $body);
}
?>

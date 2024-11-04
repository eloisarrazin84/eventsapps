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
    $medList = '';
    foreach ($expiringMeds as $med) {
        $medList .= "<li>" . htmlspecialchars($med['nom']) . " - Lot: " . htmlspecialchars($med['numero_lot']) . ", Expire le: " . htmlspecialchars($med['date_expiration']) . ", Lieu: " . htmlspecialchars($med['location_name']) . "</li>";
    }
    // Envoyer l'e-mail avec la liste des médicaments
    $emailService = new EmailService();
    $emailService->sendEmail(
        'contact@outdoorsecours.fr', 
        'Récapitulatif des Médicaments Expirants', 
        'med_expiration_report', // Nom du modèle
        ['medicaments' => $medList] // Variables à remplacer dans le modèle
    );
} else {
    // Pas de médicaments expirants
    $body = '<li>Aucun médicament n\'est en cours d\'expiration.</li>';
    $emailService = new EmailService();
    $emailService->sendEmail(
        'contact@outdoorsecours.fr', 
        'Récapitulatif des Médicaments Expirants', 
        'med_expiration_report', 
        ['medicaments' => $body] // Variables à remplacer dans le modèle
    );
}
?>

<?php
require 'EmailService.php'; // Assurez-vous que votre service d'envoi d'e-mails est correctement inclus
require_once 'EmailTemplate.php'; // Incluez la classe EmailTemplate

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

// Préparer la liste des médicaments
$medList = '';
if (!empty($expiringMeds)) {
    foreach ($expiringMeds as $med) {
        $medList .= "<li>" . htmlspecialchars($med['nom']) . " - Lot: " . htmlspecialchars($med['numero_lot']) . ", Expire le: " . htmlspecialchars($med['date_expiration']) . ", Lieu: " . htmlspecialchars($med['location_name']) . "</li>";
    }
} else {
    $medList = '<li>Aucun médicament n\'est en cours d\'expiration.</li>';
}

// Charger le modèle d'e-mail
$emailTemplate = 'med_expiration_report'; // Nom du fichier sans extension
$userVariables = ['medicaments' => $medList]; // Passer la liste des médicaments

// Charger le corps de l'e-mail
$body = EmailTemplate::loadTemplate($emailTemplate, $userVariables);

// Envoyer l'e-mail
$emailService = new EmailService();
$emailService->sendEmail('contact@outdoorsecours.fr', 'Récapitulatif des Médicaments Expirants', $body);
?>

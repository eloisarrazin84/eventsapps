<?php

require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/EmailTemplate.php'; // Assurez-vous que ce fichier existe et est accessible

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

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$expiringMeds = getExpiringMeds($conn);

$emailTemplate = 'med_expiration_report'; // Nom du modèle d'email à utiliser

try {
    if (!empty($expiringMeds)) {
        $medList = '';
        foreach ($expiringMeds as $med) {
            $medList .= "<li>" . htmlspecialchars($med['nom']) . " - Lot: " . htmlspecialchars($med['numero_lot']) . ", Expire le: " . htmlspecialchars($med['date_expiration']) . ", Lieu: " . htmlspecialchars($med['location_name']) . "</li>";
        }
        $body = EmailTemplate::loadTemplate($emailTemplate, ['medicaments' => $medList]);
    } else {
        $body = EmailTemplate::loadTemplate($emailTemplate, ['medicaments' => '<li>Aucun médicament n\'est en cours d\'expiration.</li>']);
    }

    $emailService = new EmailService();
    $emailService->sendEmail('contact@outdoorsecours.fr', 'Récapitulatif des Médicaments Expirants', $body);
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage(); // Afficher l'erreur
}
?>

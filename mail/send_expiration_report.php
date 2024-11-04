<?php
require 'EmailService.php'; // Assurez-vous que votre service d'envoi d'e-mails est correctement inclus

function loadEmailTemplate($templateFile, $data) {
    if (!file_exists($templateFile)) {
        throw new Exception("Le fichier modèle n'existe pas: $templateFile");
    }
    $template = file_get_contents($templateFile);
    return str_replace('{{medicaments}}', $data, $template);
}

function getExpiringMeds($conn) {
    $stmt = $conn->prepare("
        SELECT nom, date_expiration, numero_lot, sl.location_name 
        FROM medicaments m 
        JOIN stock_locations sl ON m.stock_location_id = sl.id 
        WHERE m.date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    // Connexion à la base de données
    $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les médicaments qui expirent
    $expiringMeds = getExpiringMeds($conn);

    // Préparer le contenu du corps de l'e-mail
    if (!empty($expiringMeds)) {
        $medList = '';
        foreach ($expiringMeds as $med) {
            $medList .= "<li>" . htmlspecialchars($med['nom']) . 
                        " - Lot: " . htmlspecialchars($med['numero_lot']) . 
                        ", Expire le: " . htmlspecialchars($med['date_expiration']) . 
                        ", Lieu: " . htmlspecialchars($med['location_name']) . "</li>";
        }

        // Charger le modèle d'e-mail et y insérer les médicaments
        $body = loadEmailTemplate('email_templates/med_expiration_report.html', $medList);
    } else {
        // Pas de médicaments expirants
        $body = loadEmailTemplate('email_templates/med_expiration_report.html', 
                                   '<li>Aucun médicament n\'est en cours d\'expiration.</li>');
    }

    // Envoyer l'e-mail
    $emailService = new EmailService();
    $emailService->sendEmail('contact@outdoorsecours.fr', 'Récapitulatif des Médicaments Expirants', $body);

} catch (Exception $e) {
    error_log("Erreur lors de l'envoi de l'e-mail: " . $e->getMessage());
}
?>

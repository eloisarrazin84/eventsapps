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
try {
    $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    exit();
}

// Récupérer les médicaments qui expirent
$expiringMeds = getExpiringMeds($conn);

// Modèle d'e-mail en HTML
$emailTemplate = '
<!DOCTYPE html>
<html>
<head>
    <title>Récapitulatif des Médicaments Expirants</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .med-list { margin: 20px 0; }
        .med-list li { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Récapitulatif des Médicaments Expirants</h1>
    <ul class="med-list">{{medicaments}}</ul>
</body>
</html>
';

if (!empty($expiringMeds)) {
    $medList = '';
    foreach ($expiringMeds as $med) {
        $medList .= "<li>" . htmlspecialchars($med['nom']) . " - Lot: " . htmlspecialchars($med['numero_lot']) . ", Expire le: " . htmlspecialchars($med['date_expiration']) . ", Lieu: " . htmlspecialchars($med['location_name']) . "</li>";
    }

    // Insérer la liste des médicaments dans le modèle
    $body = str_replace('{{medicaments}}', $medList, $emailTemplate);
} else {
    // Pas de médicaments expirants
    $body = str_replace('{{medicaments}}', '<li>Aucun médicament n\'est en cours d\'expiration.</li>', $emailTemplate);
}

// Envoyer l'e-mail
$emailService = new EmailService();
if ($emailService->sendEmail('contact@outdoorsecours.fr', 'Récapitulatif des Médicaments Expirants', $body)) {
    echo "L'e-mail a été envoyé avec succès.";
} else {
    echo "Erreur lors de l\'envoi de l\'e-mail.";
}
?>


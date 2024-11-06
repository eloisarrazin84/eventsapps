<?php

require_once __DIR__ . '/EmailService.php';

function getExpiringMeds($conn)
{
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

$emailTemplate = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif des Médicaments Expirants</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
            width: 150px; 
        }
        .med-list {
            margin: 20px 0;
            padding: 0;
            list-style-type: none;
        }
        .med-list li {
            margin-bottom: 15px;
            padding: 15px;
            background: #e7f3fe;
            border-left: 4px solid #2196F3;
            border-radius: 4px;
            position: relative; 
            transition: background 0.3s;
        }
        .med-list li:hover {
            background: #d0e7ff; 
        }
        .med-list li:before {
            content: "";
            background: url("uploads/img/pill.png") no-repeat; 
            position: absolute;
            left: 10px; 
            top: 50%; 
            transform: translateY(-50%); 
            width: 24px; 
            height: 24px; 
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .action-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s;
        }
        .action-button:hover {
            background-color: #1e88e5; 
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png" alt="Logo" class="logo">
        <h1>Récapitulatif des Médicaments Expirants</h1>
        <ul class="med-list">{{medicaments}}</ul>
        <a href="https://event.outdoorsecours.fr/dashboard_medicaments.php" class="action-button">Voir plus de détails</a>
        <div class="footer">
            <p>Cet e-mail a été envoyé par votre système de gestion de médicaments.</p>
        </div>
    </div>
</body>
</html>
';

if (!empty($expiringMeds)) {
    $medList = '';
    foreach ($expiringMeds as $med) {
        $medList .= "<li>" . htmlspecialchars($med['nom']) . " - Lot: " . htmlspecialchars($med['numero_lot']) . ", Expire le: " . htmlspecialchars($med['date_expiration']) . ", Lieu: " . htmlspecialchars($med['location_name']) . "</li>";
    }
    $body = str_replace('{{medicaments}}', $medList, $emailTemplate);
} else {
    $body = str_replace('{{medicaments}}', '<li>Aucun médicament n\'est en cours d\'expiration.</li>', $emailTemplate);
}

$emailService = new EmailService();
$emailService->sendEmail('contact@outdoorsecours.fr', 'Récapitulatif des Médicaments Expirants', $body);
?>

